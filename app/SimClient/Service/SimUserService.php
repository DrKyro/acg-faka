<?php
declare(strict_types=1);

namespace App\SimClient\Service;

use App\SimClient\Model\SimUser;
use App\Model\User;
use App\Util\Context;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;

class SimUserService
{
    private array $config;

    public function __construct()
    {
        $this->config = config('sim_client');
    }

    /**
     * 获取当前sim用户（基于主站用户认证）
     * @throws JSONException
     */
    public function getCurrentUser(): SimUser
    {
        $mainUser = $this->getMainUser();
        if (!$mainUser) {
            throw new JSONException('用户未登录');
        }

        return SimUser::findOrCreateByUserId($mainUser->id);
    }

    /**
     * 获取主站用户
     */
    private function getMainUser(): ?User
    {
        return Context::get(\App\Consts\User::SESSION);
    }

    /**
     * 检查用户点数是否足够
     * @throws JSONException
     */
    public function checkPoints(int $userId, ?float $points = null): bool
    {
        $requiredPoints = $points ?? $this->config['points_per_order'] ?? 10;
        $minPoints = $this->config['min_points'] ?? 10;

        if ($requiredPoints < $minPoints) {
            throw new JSONException('接码点数设置错误');
        }

        $simUser = SimUser::where('user_id', $userId)->first();
        if (!$simUser) {
            return false;
        }

        return $simUser->hasEnoughPoints($requiredPoints);
    }

    /**
     * 使用兑换码充值
     * @throws JSONException|RuntimeException
     */
    public function rechargeWithCode(string $code): array
    {
        $mainUser = $this->getMainUser();
        if (!$mainUser) {
            throw new JSONException('用户未登录');
        }

        $simUser = $this->getCurrentUser();
        $rechargeCode = \App\SimClient\Model\RechargeCode::findByCode($code);

        if (!$rechargeCode) {
            throw new JSONException('兑换码不存在');
        }

        if (!$rechargeCode->isValid()) {
            throw new JSONException('兑换码无效或已过期');
        }

        // 使用兑换码
        if (!$rechargeCode->use($simUser->id)) {
            throw new RuntimeException('兑换码使用失败');
        }

        // 增加点数
        $simUser->addPoints((float)$rechargeCode->points);

        // 记录充值日志
        \App\SimClient\Model\RechargeLog::create([
            'user_id' => $simUser->id,
            'points' => $rechargeCode->points,
            'code' => $code,
        ]);

        return [
            'success' => true,
            'points' => $rechargeCode->points,
            'new_balance' => $simUser->fresh()->points,
        ];
    }

    /**
     * 扣费点数
     * @throws JSONException
     */
    public function deductPoints(int $orderId, ?float $points = null): \App\SimClient\Model\DeductLog
    {
        $simUser = $this->getCurrentUser();
        $points = $points ?? ($this->config['points_per_order'] ?? 10);

        if (!$simUser->deductPoints($points)) {
            throw new JSONException('点数不足');
        }

        // 记录扣费日志
        return \App\SimClient\Model\DeductLog::createDeduct($simUser->id, $orderId, $points);
    }

    /**
     * 退还点数
     * @throws JSONException
     */
    public function refundPoints(int $orderId, ?float $points = null): bool
    {
        $simUser = $this->getCurrentUser();
        $points = $points ?? ($this->config['points_per_order'] ?? 10);

        // 增加可用点数，但不计入累计获得
        $simUser->addPoints($points, false);

        // 查找并更新扣费记录状态
        $deductLog = \App\SimClient\Model\DeductLog::where('order_id', $orderId)
            ->where('user_id', $simUser->id)
            ->where('status', \App\SimClient\Model\DeductLog::STATUS_PENDING)
            ->first();

        if ($deductLog) {
            $deductLog->markFailed();
        }

        return true;
    }

    /**
     * 获取用户点数信息
     */
    public function getUserInfo(): array
    {
        $simUser = $this->getCurrentUser();
        $mainUser = $this->getMainUser();
        
        return [
            'user_id' => $simUser->user_id,
            'username' => $mainUser->username ?? '',
            'points' => (float)$simUser->points,
            'total_points' => (float)$simUser->total_points,
        ];
    }

    /**
     * 获取用户操作日志
     */
    public function getUserLogs(): array
    {
        $simUser = $this->getCurrentUser();
        
        return [
            'recharge_logs' => \App\SimClient\Model\RechargeLog::getUserLogs($simUser->id, 10),
            'deduct_logs' => \App\SimClient\Model\DeductLog::getUserLogs($simUser->id, 10),
        ];
    }
}
