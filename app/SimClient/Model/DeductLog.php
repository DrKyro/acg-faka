<?php
declare(strict_types=1);

namespace App\SimClient\Model;

use Illuminate\Database\Eloquent\Model;

class DeductLog extends Model
{
    protected $table = 'sim_client_deduct_log';

    protected $guarded = [];

    public $timestamps = false;

    protected $casts = [
        'points' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    protected $fillable = [
        'user_id',
        'order_id', 
        'points',
        'status',
    ];

    /**
     * 扣费状态常量
     */
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    /**
     * 创建扣费记录
     */
    public static function createDeduct(int $userId, int $orderId, float $points): self
    {
        return self::create([
            'user_id' => $userId,
            'order_id' => $orderId,
            'points' => $points,
            'status' => self::STATUS_PENDING,
        ]);
    }

    /**
     * 更新扣费状态
     */
    public function updateStatus(string $status): bool
    {
        if (!in_array($status, [self::STATUS_PENDING, self::STATUS_COMPLETED, self::STATUS_FAILED])) {
            return false;
        }
        
        $this->status = $status;
        return $this->save();
    }

    /**
     * 将扣费记录标记为已完成
     */
    public function markCompleted(): bool
    {
        $this->status = self::STATUS_COMPLETED;
        return $this->save();
    }

    /**
     * 将扣费记录标记为失败（退还）
     */
    public function markFailed(): bool
    {
        $this->status = self::STATUS_FAILED;
        return $this->save();
    }

    /**
     * 获取用户扣费记录
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
