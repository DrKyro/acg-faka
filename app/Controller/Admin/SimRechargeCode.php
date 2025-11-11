<?php
declare(strict_types=1);

namespace App\Controller\Admin;


use App\Controller\Base\View\Manage;
use App\Interceptor\ManageSession;
use App\SimClient\Model\RechargeCode;
use App\Util\Date;
use Carbon\Carbon;
use Kernel\Annotation\Interceptor;
use Kernel\Exception\ViewException;
use Kernel\Exception\JSONException;

#[Interceptor(ManageSession::class)]
class SimRechargeCode extends Manage
{
    /**
     * @throws ViewException
     */
    public function index(): string
    {
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
        
        $perPage = (int)($_GET['per_page'] ?? 20);
        $perPage = min($perPage, 100); // 限制最大每页数量
        $codes = $query->paginate($perPage);
        
        // 获取筛选参数，用于视图显示
        $filters = [
            'code' => $_GET['code'] ?? '',
            'status' => $_GET['status'] ?? '',
        ];
        
        return $this->render("SIM兑换码管理", "SimRechargeCode/Index.html", [
            'codes' => $codes,
            'stats' => [
                'total' => RechargeCode::count(),
                'used' => RechargeCode::where('is_used', 1)->count(),
                'unused' => RechargeCode::where('is_used', 0)->count(),
                'expired' => RechargeCode::where('expires_at', '<', Date::current())->count(),
            ],
            'filters' => $filters
        ]);
    }

    /**
     * 生成兑换码页面
     */
    public function generate(): string
    {
        return $this->render("生成兑换码", "SimRechargeCode/Generate.html");
    }

    /**
     * 生成兑换码
     */
    public function doGenerate(): array
    {
        $points = (float)($_POST['points'] ?? 0);
        $count = (int)($_POST['count'] ?? 1);
        $expireDays = (int)($_POST['expire_days'] ?? 0);

        if ($points <= 0) {
            throw new JSONException('点数必须大于0');
        }

        if ($count <= 0 || $count > 100) {
            throw new JSONException('生成数量必须在1-100之间');
        }

        $expiresAt = null;
        if ($expireDays > 0) {
            $expiresAt = Carbon::now()->addDays($expireDays)->toDateTimeString();
        }

        $codes = RechargeCode::generateBatch($points, $count, $expiresAt);
        
        return $this->json(200, "成功生成 {$count} 个兑换码", [
            'codes' => $codes,
            'total' => count($codes)
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
        
        return $this->json(200, '删除成功');
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
        
        return $this->json(200, '批量删除成功');
    }

    /**
     * 兑换码详情
     */
    public function view(): string
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            throw new \Exception('参数错误');
        }

        $code = RechargeCode::with('usedUser')->find($id);
        if (!$code) {
            throw new \Exception('兑换码不存在');
        }

        return $this->render("兑换码详情", "SimRechargeCode/View.html", [
            'code' => $code
        ]);
    }
}
