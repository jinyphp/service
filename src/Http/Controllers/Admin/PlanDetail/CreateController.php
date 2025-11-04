<?php

namespace Jiny\Service\Http\Controllers\Admin\PlanDetail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\ServicePlan;
use Jiny\Service\Models\ServicePlanDetail;

class CreateController extends Controller
{
    public function __invoke(Request $request, $planId)
    {
        $plan = ServicePlan::with('service')->findOrFail($planId);

        // 상세 타입 옵션
        $detailTypes = ServicePlanDetail::getDetailTypes();

        // 값 타입 옵션
        $valueTypes = ServicePlanDetail::getValueTypes();

        // 카테고리 옵션
        $categories = ServicePlanDetail::getCategories();

        // 기존 그룹명들 (현재 플랜의)
        $existingGroups = ServicePlanDetail::where('service_plan_id', $planId)
                            ->whereNotNull('group_name')
                            ->distinct()
                            ->pluck('group_name')
                            ->filter()
                            ->sort()
                            ->values();

        // 다음 정렬순서 제안
        $nextPos = ServicePlanDetail::where('service_plan_id', $planId)->max('pos') + 1;

        // 기본 아이콘 템플릿
        $defaultIcons = [
            'feature' => [
                'fas fa-check-circle' => '체크 (기본)',
                'fas fa-star' => '별',
                'fas fa-thumbs-up' => '좋아요',
                'fas fa-heart' => '하트',
                'fas fa-trophy' => '트로피',
                'fas fa-gem' => '다이아몬드',
                'fas fa-crown' => '왕관',
            ],
            'limitation' => [
                'fas fa-times-circle' => 'X (기본)',
                'fas fa-exclamation-triangle' => '경고',
                'fas fa-ban' => '금지',
                'fas fa-lock' => '잠금',
                'fas fa-minus-circle' => '마이너스',
            ],
            'requirement' => [
                'fas fa-exclamation-circle' => '느낌표 (기본)',
                'fas fa-info-circle' => '정보',
                'fas fa-question-circle' => '물음표',
                'fas fa-clipboard-list' => '체크리스트',
            ],
        ];

        return view('jiny-service::admin.plan_detail.create', compact(
            'plan',
            'detailTypes',
            'valueTypes',
            'categories',
            'existingGroups',
            'nextPos',
            'defaultIcons'
        ));
    }
}