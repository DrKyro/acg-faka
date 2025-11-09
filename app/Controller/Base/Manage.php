<?php
declare(strict_types=1);

namespace App\Controller\Base;


use App\Util\Context;

abstract class Manage
{
    /**
     * 获取管理员对象数据
     * @return \App\Model\Manage|null
     */
    public function getManage(): ?\App\Model\Manage
    {
        return Context::get(\App\Consts\Manage::SESSION);
    }

    /**
     * 构造统一的 JSON 响应
     */
    protected function json(int $code, ?string $message = null, ?array $data = []): array
    {
        $payload = ['code' => $code];
        if ($message !== null) {
            $payload['msg'] = $message;
        }
        $payload['data'] = $data;
        return $payload;
    }
}
