<?php
declare(strict_types=1);

namespace App\Controller\Admin\Api;


use App\Controller\Base\API\Manage;
use App\Interceptor\ManageSession;
use App\SimClient\Model\RechargeCode;
use App\Util\Date;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Exception\JSONException;

#[Interceptor(ManageSession::class, Interceptor::TYPE_API)]
class SimRechargeCode extends Manage
{
    /**
     * 获取兑换码列表
     */
    public function list(): array
    {
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 20);
        
        // 限制每页最大数量
        $limit = min($limit, 100);
        
        $query = RechargeCode::query()->orderBy('created_at', 'desc');
        
        // 搜索条件
        if (!empty($_GET['code'])) {
            $query->where('code', 'like', '%' . $_GET['code'] . '%');
        }
        
        if (isset($_GET['status']) && $_GET['status'] !== '') {
            $status = (int)$_GET['status'];
            if ($status === 0) {
                // 未使用
                $query->where('is_used', 0);
            } elseif ($status === 1) {
                // 已使用
                $query->where('is_used', 1);
            } elseif ($status === 2) {
                // 已过期
                $query->where('expires_at', '<', Date::current());
            }
        }
        
        $total = $query->count();
        $codes = $query->offset(($page - 1) * $limit)
                        ->limit($limit)
                        ->get()
                        ->map(function(RechargeCode $code) {
                            return [
                                'id' => $code->id,
                                'code' => $code->code,
                                'points' => (float)$code->points,
                                'is_used' => $code->is_used,
                                'used_user_id' => $code->used_user_id,
                                'used_at' => $code->used_at,
                                'expires_at' => $code->expires_at,
                                'created_at' => $code->created_at,
                                'status_text' => $code->is_used ? '已使用' : 
                                                 ($code->expires_at && $code->expires_at < Date::current() ? '已过期' : '未使用')
                            ];
                        });
        
        return $this->json(0, null, [
            'data' => $codes,
            'total' => $total,
            'code' => 0,
            'msg' => null
        ]);
    }

    /**
     * 删除兑换码
     */
    public function delete(): array
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            throw new JSONException('参数错误');
        }

        $code = RechargeCode::find($id);
        if (!$code) {
            throw new JSONException('兑换码不存在');
        }

        $code->delete();
        
        return $this->json(0, '删除成功', null);
    }

    /**
     * 批量删除
     */
    public function batchDelete(): array
    {
        $ids = $_POST['ids'] ?? [];
        if (empty($ids)) {
            throw new JSONException('请选择要删除的兑换码');
        }

        RechargeCode::whereIn('id', $ids)->delete();
        
        return $this->json(0, '批量删除成功', null);
    }

    /**
     * 获取兑换码详情
     */
    public function detail(): array
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            throw new JSONException('参数错误');
        }

        $code = RechargeCode::with('usedUser')->find($id);
        if (!$code) {
            throw new JSONException('兑换码不存在');
        }
        
        return $this->json(0, null, [
            'code' => [
                'id' => $code->id,
                'code' => $code->code,
                'points' => (float)$code->points,
                'is_used' => $code->is_used,
                'used_user_id' => $code->used_user_id,
                'used_at' => $code->used_at,
                'used_user' => $code->usedUser ? [
                    'id' => $code->usedUser->id,
                    'username' => $code->usedUser->username
                ] : null,
                'expires_at' => $code->expires_at,
                'created_at' => $code->created_at
            ]
        ]);
    }

    /**
     * 获取统计信息
     */
    public function stats(): array
    {
        return $this->json(0, null, [
            'total' => RechargeCode::count(),
            'used' => RechargeCode::where('is_used', 1)->count(),
            'unused' => RechargeCode::where('is_used', 0)->count(),
            'expired' => RechargeCode::where('expires_at', '<', Date::current())->count(),
        ]);
    }
}
