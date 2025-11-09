<?php
declare(strict_types=1);

namespace App\SimClient\Model;

use Illuminate\Database\Eloquent\Model;

class RechargeLog extends Model
{
    protected $table = 'sim_client_recharge_log';

    protected $guarded = [];

    public $timestamps = false;

    protected $casts = [
        'points' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    /**
     * 获取用户充值记录
     */
    public static function getUserLogs(int $userId, int $limit = 20): array
    {
        return self::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
