<?php
declare(strict_types=1);

namespace App\Controller\User\Api;

use App\Controller\Base\API\User;
use App\Interceptor\UserSession;
use App\Interceptor\Waf;
use App\SimClient\Service\OrderService;
use App\SimClient\Service\SimUserService;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;

#[Interceptor([Waf::class, UserSession::class], Interceptor::TYPE_API)]
class SimClient extends User
{
    #[Inject]
    private OrderService $orderService;

    #[Inject]
    private SimUserService $simUserService;

    /**
     * @throws JSONException|RuntimeException
     */
    public function order(): array
    {
        $order = $this->orderService->create();
        return $this->json(200, '已申请新号码', [
            'order' => $this->orderService->format($order),
            'orders' => $this->orderService->list(),
        ]);
    }

    public function history(): array
    {
        // 检查并处理超时订单
        $this->orderService->checkTimeoutOrders();
        
        return $this->json(200, null, [
            'orders' => $this->orderService->list(),
        ]);
    }

    /**
     * @throws JSONException|RuntimeException
     */
    public function status(): array
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id === 0) {
            throw new JSONException('参数错误');
        }
        $order = $this->orderService->refresh($id);
        return $this->json(200, null, [
            'order' => $this->orderService->format($order),
        ]);
    }

    /**
     * @throws JSONException|RuntimeException
     */
    public function cancel(): array
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id === 0) {
            throw new JSONException('参数错误');
        }
        $order = $this->orderService->cancel($id);
        return $this->json(200, '已取消', [
            'order' => $this->orderService->format($order),
        ]);
    }

    /**
     * 获取用户信息和点数
     */
    public function userInfo(): array
    {
        $userInfo = $this->simUserService->getUserInfo();
        return $this->json(200, null, $userInfo);
    }

    /**
     * 使用兑换码充值
     * @throws JSONException|RuntimeException
     */
    public function recharge(): array
    {
        $code = trim($_POST['code'] ?? '');
        if (empty($code)) {
            throw new JSONException('请输入兑换码');
        }

        $result = $this->simUserService->rechargeWithCode($code);
        return $this->json(200, '充值成功', $result);
    }

    /**
     * 获取用户操作日志
     */
    public function logs(): array
    {
        $logs = $this->simUserService->getUserLogs();
        return $this->json(200, null, $logs);
    }

    /**
     * 检查点数是否足够接码
     */
    public function checkPoints(): array
    {
        $userInfo = $this->simUserService->getUserInfo();
        $config = config('sim_client');
        $pointsPerOrder = $config['points_per_order'] ?? 10;
        
        return $this->json(200, null, [
            'has_enough' => $userInfo['points'] >= $pointsPerOrder,
            'current_points' => $userInfo['points'],
            'required_points' => $pointsPerOrder,
        ]);
    }

    /**
     * 获取扣费记录
     */
    public function deductLogs(): array
    {
        $logs = $this->simUserService->getUserLogs();
        return $this->json(200, null, [
            'deduct_logs' => $logs['deduct_logs'],
        ]);
    }
}
