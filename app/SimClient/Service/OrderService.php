<?php
declare(strict_types=1);

namespace App\SimClient\Service;

use App\SimClient\Enum\OrderStatus;
use App\SimClient\Model\SimOrder;
use App\SimClient\Support\SchemaMigrator;
use App\SimClient\Support\SessionContext;
use App\Util\Context;
use App\Util\Date;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;

class OrderService
{
    private array $config;
    private bool $tableReady = false;

    public function __construct()
    {
        $this->config = config('sim_client');
        $this->ensureTable();
    }

    /**
     * @return SimOrder
     * @throws JSONException|RuntimeException
     */
    public function create(): SimOrder
    {
        $user = $this->getCurrentUser();
        $sessionId = SessionContext::id();
        $this->ensureTable();
        
        // 检查进行中的订单数量
        $waiting = SimOrder::query()->userId($user->id)->where('status', OrderStatus::WAITING)->count();
        if ($waiting >= 3) {
            throw new JSONException('进行中的订单过多，请先等待现有订单完成');
        }

        // 获取5sim服务
        $client = $this->client();
        
        // 先创建临时订单ID用于扣费记录
        $simUserService = new SimUserService();
        $pointsPerOrder = $this->config['points_per_order'] ?? 10;
        
        // 购买号码
        $order = $client->buyActivation(
            $this->config['country'],
            $this->config['operator'],
            $this->config['product'],
        );

        if (!isset($order['id'], $order['phone'])) {
            throw new RuntimeException('5sim 返回数据错误');
        }

        // 创建订单，关联用户
        $simOrder = SimOrder::query()->create([
            'user_id' => $user->id,
            'fivesim_order_id' => (string)$order['id'],
            'phone' => (string)$order['phone'],
            'status' => OrderStatus::WAITING,
            'sms_content' => null,
            'cost' => (float)($order['price'] ?? 0),
            'country' => (string)($order['country'] ?? $this->config['country']),
            'operator' => (string)($order['operator'] ?? $this->config['operator']),
            'product' => (string)($order['product'] ?? $this->config['product']),
            'create_time' => Date::current(),
        'update_time' => Date::current(),
            'expire_time' => isset($order['expires']) ? date('Y-m-d H:i:s', strtotime((string)$order['expires'])) : null,
        ]);

        // 在成功创建订单后立即扣费
        $simUserService->deductPoints($simOrder->id, $pointsPerOrder);

        return $simOrder;
    }

    /**
     * @throws JSONException|RuntimeException
     */
    public function refresh(int $id): SimOrder
    {
        $order = $this->findOwned($id);
        $wasWaiting = in_array($order->status, [OrderStatus::WAITING], true);
        
        if (!$wasWaiting) {
            return $order;
        }

        $client = $this->client();
        $remote = $client->checkOrder($order->fivesim_order_id);

        $status = $this->mapStatus((string)($remote['status'] ?? ''));
        $smsText = $this->extractSms($remote);

        if ($status === OrderStatus::RECEIVED && $smsText === null) {
            $status = OrderStatus::WAITING;
            $remote['status'] = OrderStatus::WAITING;
        }

        $order->status = $status;
        if ($smsText !== null) {
            $order->sms_content = $smsText;
        }
        $order->update_time = Date::current();
        $order->save();

        // 如果订单状态变为失败/超时等非完成状态，且之前是等待状态，进行退还
        if ($wasWaiting && !$order->isCompleted() && $status !== OrderStatus::WAITING) {
            $simUserService = new SimUserService();
            $simUserService->refundPoints($order->id);
        }
        
        // 如果订单已完成，将扣费记录标记为已完成
        if ($wasWaiting && $order->isCompleted()) {
            $deductLog = \App\SimClient\Model\DeductLog::where('order_id', $order->id)
                ->where('status', \App\SimClient\Model\DeductLog::STATUS_PENDING)
                ->first();
                
            if ($deductLog) {
                $deductLog->markCompleted();
            }
        }

        // 注意：扣费已经在创建订单时完成，现在只是更新记录状态

        return $order;
    }

    /**
     * @throws JSONException|RuntimeException
     */
    public function cancel(int $id): SimOrder
    {
        $order = $this->findOwned($id);
        if ($order->status !== OrderStatus::WAITING) {
            return $order;
        }

        $client = $this->client();
        $client->cancelOrder($order->fivesim_order_id);
        $order->status = OrderStatus::CANCELED;
        $order->update_time = Date::current();
        $order->save();

        // 取消订单时退还点数
        $simUserService = new SimUserService();
        $simUserService->refundPoints($order->id);

        return $order;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function list(): array
    {
        $user = $this->getCurrentUser();
        $this->ensureTable();
        return SimOrder::query()
            ->userId($user->id)
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->map(fn(SimOrder $order) => $this->format($order))
            ->toArray();
    }

    /**
     * @throws JSONException
     */
    private function findOwned(int $id): SimOrder
    {
        $user = $this->getCurrentUser();
        $this->ensureTable();
        $order = SimOrder::query()
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->where('is_deleted', 0)
            ->first();

        if (!$order) {
            throw new JSONException('记录不存在或已删除');
        }

        return $order;
    }

    /**
     * 获取当前主站用户
     */
    private function getCurrentUser(): ?\App\Model\User
    {
        return Context::get(\App\Consts\User::SESSION);
    }

    /**
     * @throws RuntimeException
     */
    private function client(): FiveSimClient
    {
        return new FiveSimClient($this->config['token'] ?? '');
    }

    private function ensureTable(): void
    {
        if ($this->tableReady) {
            return;
        }
        SchemaMigrator::ensurePhoneCodeOrderTable(SimOrder::TABLE);
        $this->tableReady = true;
    }

    private function mapStatus(string $status): string
    {
        return match (strtoupper($status)) {
            'PENDING', 'WAITING' => OrderStatus::WAITING,
            'RECEIVED', 'FINISHED' => OrderStatus::RECEIVED,
            'CANCELED' => OrderStatus::CANCELED,
            'TIMEOUT' => OrderStatus::TIMEOUT,
            default => OrderStatus::ERROR,
        };
    }

    private function extractSms(array $remote): ?string
    {
        $smsList = Arr::get($remote, 'sms', []);
        if (is_array($smsList) && isset($smsList[0]['text'])) {
            return (string)$smsList[0]['text'];
        }

        return null;
    }

    public function format(SimOrder $order): array
    {
        $rawStatus = strtoupper((string)($order->status ?? ''));
        $useRemoteStatus = $rawStatus !== '' && $order->status !== OrderStatus::WAITING;
        $statusKey = $useRemoteStatus ? $rawStatus : $order->status;
        $statusText = $useRemoteStatus ? $rawStatus : OrderStatus::label($order->status);
        $statusClass = $this->statusClass($statusKey);
        return [
            'id' => $order->id,
            'phone' => $order->phone,
            'status' => $order->status,
            'status_text' => $statusText,
            'status_class' => $statusClass,
            'sms_text' => $order->sms_content,
            'created_at' => $order->create_time?->format('Y-m-d H:i:s'),
        ];
    }

    private function statusClass(string $status): string
    {
        $status = strtoupper($status);
        return match (true) {
            str_contains($status, 'WAIT') || $status === OrderStatus::WAITING => 'status-waiting',
            str_contains($status, 'RECEIVED') || str_contains($status, 'FINISHED') || $status === OrderStatus::RECEIVED => 'status-done',
            str_contains($status, 'CANCEL') || $status === OrderStatus::CANCELED => 'status-cancel',
            str_contains($status, 'TIME') || $status === OrderStatus::TIMEOUT => 'status-timeout',
            default => 'status-error',
        };
    }

    /**
     * 检查并处理超时订单
     */
    public function checkTimeoutOrders(): void
    {
        $timeoutMinutes = $this->config['poll_timeout'] ?? 180; // 3小时
        $cutoffTime = date('Y-m-d H:i:s', strtotime(Date::current()) - ($timeoutMinutes * 60));

        $timeoutOrders = SimOrder::query()
            ->where('status', OrderStatus::WAITING)
            ->where('create_time', '<', $cutoffTime)
            ->get();

        foreach ($timeoutOrders as $order) {
            try {
                // 标记为超时
                $order->status = OrderStatus::TIMEOUT;
                $order->save();

                // 退还点数
                $simUserService = new SimUserService();
                $simUserService->refundPoints($order->id);
            } catch (\Exception $e) {
                // 记录错误但不中断处理
                error_log("Timeout order refund failed: " . $e->getMessage());
            }
        }
    }
}
