<?php

namespace Jiny\Service\Http\Controllers\Admin\PlanDetail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\ServicePlan;
use Jiny\Service\Models\ServicePlanDetail;

class DestroyController extends Controller
{
    public function __invoke(Request $request, $planId, $detailId)
    {
        $plan = ServicePlan::findOrFail($planId);

        $detail = ServicePlanDetail::where('service_plan_id', $planId)
                    ->findOrFail($detailId);

        // 삭제 실행 (Soft Delete)
        $detail->delete();

        return redirect()
            ->route('admin.service.plan.detail.index', $planId)
            ->with('success', '플랜 상세 정보가 성공적으로 삭제되었습니다.');
    }
}