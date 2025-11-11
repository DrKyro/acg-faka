<?php
declare(strict_types=1);

namespace App\SimClient\Model;

use Illuminate\Database\Eloquent\Model;

class SimUser extends Model
{
    protected $table = 'sim_client_user';

    protected $guarded = [];

    protected $casts = [
        'points' => 'decimal:2',
        'total_points' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 根据主站用户ID获取或创建sim用户
     */
    public static function findOrCreateByUserId(int $userId): self
    {
        $simUser = self::where('user_id', $userId)->first();
        
        if (!$simUser) {
            $simUser = self::create([
                'user_id' => $userId,
                'points' => 0.00,
                'total_points' => 0.00,
            ]);
        }
        
        return $simUser;
    }

    /**
     * 增加点数
     */
    public function addPoints(float $points, bool $countToTotal = true): bool
    {
        $this->points += $points;
        if ($countToTotal) {
            $this->total_points += $points;
        }
        return $this->save();
    }

    /**
     * 扣除点数
     */
    public function deductPoints(float $points): bool
    {
        if ($this->points < $points) {
            return false;
        }
        
        $this->points -= $points;
        return $this->save();
    }

    /**
     * 检查点数是否足够
     */
    public function hasEnoughPoints(float $points): bool
    {
        return $this->points >= $points;
    }
}
