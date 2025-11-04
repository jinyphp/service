<?php

namespace Jiny\Service\Http\Controllers\Admin\Plan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\ServicePlan;

class DestroyController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $plan = ServicePlan::findOrFail($id);

        // 구독자가 있는지 확인
        $subscribersCount = $plan->serviceUsers()->count();

        if ($subscribersCount > 0) {
            return redirect()
                ->route('admin.service.plan.index')
                ->with('error', "이 플랜은 {$subscribersCount}명의 구독자가 있어 삭제할 수 없습니다. 먼저 플랜을 비활성화해주세요.");
        }

        // 결제 내역이 있는지 확인
        $paymentsCount = $plan->serviceUsers()
                             ->join('service_payments', 'service_users.id', '=', 'service_payments.service_user_id')
                             ->count();

        if ($paymentsCount > 0) {
            return redirect()
                ->route('admin.service.plan.index')
                ->with('error', "이 플랜은 결제 내역이 있어 삭제할 수 없습니다. 플랜을 비활성화하여 숨기는 것을 권장합니다.");
        }

        $planName = $plan->plan_name;
        $plan->delete();

        return redirect()
            ->route('admin.service.plan.index')
            ->with('success', "서비스 플랜 '{$planName}'이(가) 성공적으로 삭제되었습니다.");
    }
}