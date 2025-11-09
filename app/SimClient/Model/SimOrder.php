<?php
declare(strict_types=1);

namespace App\SimClient\Model;

use Illuminate\Database\Eloquent\Model;

class SimOrder extends Model
{
    public const TABLE = 'phone_code_order';

    protected $table = self::TABLE;

    protected $guarded = [];

    protected $casts = [
        'user_id' => 'integer',
        'cost' => 'decimal:2',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'expire_time' => 'datetime',
    ];

    // 字段映射
    public $timestamps = false;

    public function scopeUserId($query, ?int $userId = null)
    {
        if ($userId !== null) {
            return $query->where('user_id', $userId);
        }
        return $query;
    }

    /**
     * 检查订单是否成功（已收到短信）
     */
    public function isCompleted(): bool
    {
        return $this->status === 'RECEIVED' && !empty($this->sms_content);
    }
}
