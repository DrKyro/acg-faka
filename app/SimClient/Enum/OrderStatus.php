<?php
declare(strict_types=1);

namespace App\SimClient\Enum;

class OrderStatus
{
    public const WAITING = 'WAITING';
    public const RECEIVED = 'RECEIVED';
    public const CANCELED = 'CANCELED';
    public const TIMEOUT = 'TIMEOUT';
    public const ERROR = 'ERROR';

    public static function label(string $status): string
    {
        return match ($status) {
            self::WAITING => '等待短信',
            self::RECEIVED => '已获取',
            self::CANCELED => '已取消',
            self::TIMEOUT => '已超时',
            default => '异常',
        };
    }
}
