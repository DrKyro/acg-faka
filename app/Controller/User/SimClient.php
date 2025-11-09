<?php
declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\Base\User;
use App\Interceptor\UserSession;
use App\Interceptor\Waf;
use App\SimClient\Service\OrderService;
use App\SimClient\Service\SimUserService;
use App\SimClient\Support\ViewRenderer;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Exception\ViewException;

#[Interceptor([Waf::class, UserSession::class])]
class SimClient extends User
{
    #[Inject]
    private OrderService $orderService;

    #[Inject]
    private SimUserService $simUserService;

    /**
     * @throws ViewException|\SmartyException
     */
    public function index(): string
    {
        $clientConfig = config('sim_client');
        
        // 获取主站用户信息（用于header显示）
        $user = $this->getUser();
        if ($user) {
            debug("用户已登录，ID: " . $user['id']);
        } else {
            debug("用户未登录");
        }
        
        // 获取SIM用户信息
        $userInfo = [];
        if ($user) {
            try {
                debug("开始获取SIM用户信息...");
                $userInfo = $this->simUserService->getUserInfo();
                debug("SIM用户信息获取成功，点数: " . ($userInfo['points'] ?? '未知'));
            } catch (\Exception $e) {
                debug("获取SIM用户信息失败: " . $e->getMessage());
                // 用户未登录时忽略错误
            }
        }
        
        // 检查并处理超时订单
        debug("检查超时订单...");
        $this->orderService->checkTimeoutOrders();
        
        // 获取订单列表
        $orders = $this->orderService->list();
        debug("订单列表获取完成，共 " . count($orders) . " 条记录");
        
        $result = ViewRenderer::render('Index.html', [
            'title' => '英国小红书接码',
            'orders' => $orders,
            'config' => $clientConfig,
            'user' => $user, // 传递主站用户信息给header
            'user_info' => $userInfo,
        ]);
        
        return $result;
    }
}
