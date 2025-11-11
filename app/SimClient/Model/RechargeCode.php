<?php
declare(strict_types=1);

namespace App\SimClient\Model;

use App\SimClient\Support\SchemaMigrator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RechargeCode extends Model
{
    public const TABLE = 'sim_client_recharge_code';

    protected $table = self::TABLE;

    protected $guarded = [];

    protected $casts = [
        'points' => 'decimal:2',
        'is_used' => 'boolean',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    private static bool $schemaEnsured = false;

    protected static function booted(): void
    {
        if (!self::$schemaEnsured) {
            SchemaMigrator::ensureRechargeCodeTable(self::TABLE);
            self::$schemaEnsured = true;
        }
    }

    /**
     * 验证兑换码是否有效
     */
    public function isValid(): bool
    {
        // 检查是否已使用
        if ($this->is_used) {
            return false;
        }
        
        // 检查是否过期
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        
        return true;
    }

    /**
     * 使用兑换码
     */
    public function use(int $userId): bool
    {
        if (!$this->isValid()) {
            return false;
        }
        
        $this->is_used = true;
        $this->used_user_id = $userId;
        $this->used_at = Carbon::now();
        
        return $this->save();
    }

    /**
     * 根据兑换码查找
     */
    public static function findByCode(string $code): ?self
    {
        return self::where('code', $code)->first();
    }

    /**
     * 生成兑换码
     */
    public static function generate(float $points, ?string $expiresAt = null): string
    {
        $code = strtoupper(substr(md5(uniqid() . time()), 0, 12));
        
        self::create([
            'code' => $code,
            'points' => $points,
            'expires_at' => $expiresAt,
        ]);
        
        return $code;
    }

    /**
     * 获取使用用户
     */
    public function usedUser(): HasOne
    {
        return $this->hasOne(\App\Model\User::class, 'id', 'used_user_id');
    }

    /**
     * 批量生成兑换码
     */
    public static function generateBatch(float $points, int $count, ?string $expiresAt = null): array
    {
        $codes = [];
        
        for ($i = 0; $i < $count; $i++) {
            $codes[] = self::generate($points, $expiresAt);
        }
        
        return $codes;
    }
}
