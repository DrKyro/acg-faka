<?php
declare(strict_types=1);

namespace App\SimClient\Interceptor;

use App\SimClient\Service\SimUserService;
use App\Util\Context;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Method;
use Kernel\Exception\JSONException;

#[Interceptor(UserAuth::class)]
class UserAuth
{
    #[Method]
    public function handle(): void
    {
        $user = Context::get(\App\Consts\User::SESSION);
        if (!$user) {
            throw new JSONException('用户未登录');
        }
    }
}
