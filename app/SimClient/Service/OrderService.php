<?php
declare(strict_types=1);

namespace App\SimClient\Service;

use App\SimClient\Enum\OrderStatus;
use App\SimClient\Model\SimOrder;
use App\SimClient\Support\SessionContext;
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
        $sessionId = SessionContext::id();
        $this->ensureTable();
        $waiting = SimOrder::query()->session($sessionId)->where('status', OrderStatus::WAITING)->count();
        if ($waiting >= 3) {
            throw new JSONException('进行中的订单过多，请先等待现有订单完成');
        }

        $client = $this->client();
        $order = $client->buyActivation(
            $this->config['country'],
            $this->config['operator'],
            $this->config['product'],
        );

        if (!isset($order['id'], $order['phone'])) {
            throw new RuntimeException('5sim 返回数据错误');
        }

        return SimOrder::query()->create([
            'session_id' => $sessionId,
            'external_id' => (string)$order['id'],
            'phone' => (string)$order['phone'],
            'status' => OrderStatus::WAITING,
            'country' => (string)($order['country'] ?? $this->config['country']),
            'operator' => (string)($order['operator'] ?? $this->config['operator']),
            'product' => (string)($order['product'] ?? $this->config['product']),
            'price' => (float)($order['price'] ?? 0),
            'meta' => $order,
            'expires_at' => isset($order['expires']) ? date('Y-m-d H:i:s', strtotime((string)$order['expires'])) : null,
        ]);
    }

    /**
     * @throws JSONException|RuntimeException
     */
    public function refresh(int $id): SimOrder
    {
        $order = $this->findOwned($id);
        if (!in_array($order->status, [OrderStatus::WAITING], true)) {
            return $order;
        }

        $client = $this->client();
        $remote = $client->checkOrder($order->external_id);

        $status = $this->mapStatus((string)($remote['status'] ?? ''));
        $smsText = $this->extractSms($remote);

        if ($status === OrderStatus::RECEIVED && $smsText === null) {
            $status = OrderStatus::WAITING;
            $remote['status'] = OrderStatus::WAITING;
        }

        $order->status = $status;
        if ($smsText !== null) {
            $order->sms_text = $smsText;
        }
        $order->meta = $remote;
        $order->save();

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
        $client->cancelOrder($order->external_id);
        $order->status = OrderStatus::CANCELED;
        $order->save();

        return $order;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function list(): array
    {
        $sessionId = SessionContext::id();
        $this->ensureTable();
        return SimOrder::query()
            ->session($sessionId)
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
        $sessionId = SessionContext::id();
        $this->ensureTable();
        $order = SimOrder::query()
            ->where('id', $id)
            ->where('session_id', $sessionId)
            ->where('is_deleted', 0)
            ->first();

        if (!$order) {
            throw new JSONException('记录不存在或已删除');
        }

        return $order;
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
        $schema = Manager::schema();
        if (!$schema->hasTable('sim_client_order')) {
            $schema->create('sim_client_order', function (Blueprint $table) {
                $table->increments('id');
                $table->string('session_id', 64);
                $table->string('external_id', 64);
                $table->string('phone', 32);
                $table->string('status', 16);
                $table->string('country', 32)->nullable();
                $table->string('operator', 32)->nullable();
                $table->string('product', 32)->nullable();
                $table->decimal('price', 10, 2)->default(0);
                $table->text('sms_text')->nullable();
                $table->text('meta')->nullable();
                $table->dateTime('expires_at')->nullable();
                $table->boolean('is_deleted')->default(false);
                $table->timestamps();
                $table->index('session_id');
                $table->index('status');
            });
        }
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
        $rawStatus = strtoupper((string)($order->meta['status'] ?? ''));
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
            'sms_text' => $order->sms_text,
            'created_at' => $order->created_at?->format('Y-m-d H:i:s'),
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
}
