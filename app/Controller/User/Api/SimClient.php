<?php
declare(strict_types=1);

namespace App\Controller\User\Api;

use App\Controller\Base\API\User;
use App\Interceptor\Waf;
use App\SimClient\Service\OrderService;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;

#[Interceptor([Waf::class], Interceptor::TYPE_API)]
class SimClient extends User
{
    #[Inject]
    private OrderService $orderService;

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

}
