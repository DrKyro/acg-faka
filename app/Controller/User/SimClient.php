<?php
declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\Base\User;
use App\Interceptor\Waf;
use App\SimClient\Service\OrderService;
use App\SimClient\Support\ViewRenderer;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Exception\ViewException;

#[Interceptor([Waf::class])]
class SimClient extends User
{
    #[Inject]
    private OrderService $orderService;

    /**
     * @throws ViewException|\SmartyException
     */
    public function index(): string
    {
        $clientConfig = config('sim_client');
        return ViewRenderer::render('Index.html', [
            'title' => '英国小红书接码',
            'orders' => $this->orderService->list(),
            'config' => $clientConfig,
        ]);
    }
}
