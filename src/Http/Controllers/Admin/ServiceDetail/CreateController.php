<?php

namespace Jiny\Service\Http\Controllers\Admin\ServiceDetail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\SiteService;
use Jiny\Service\Models\ServicePlanDetail;

class CreateController extends Controller
{
    public function __invoke(Request $request, $serviceId)
    {
        $service = SiteService::findOrFail($serviceId);

        // 다음 정렬 순서 계산
        $nextPos = ServicePlanDetail::where('service_id', $serviceId)->max('pos') + 1;

        // 필터 옵션들
        $detailTypes = ServicePlanDetail::getDetailTypes();
        $valueTypes = ServicePlanDetail::getValueTypes();
        $categories = ServicePlanDetail::getCategories();

        // 기존 그룹명 목록 (자동완성용)
        $existingGroups = ServicePlanDetail::where('service_id', $serviceId)
                            ->whereNotNull('group_name')
                            ->distinct()
                            ->pluck('group_name')
                            ->filter()
                            ->sort()
                            ->values();

        return view('jiny-service::admin.service_detail.create', compact(
            'service',
            'nextPos',
            'detailTypes',
            'valueTypes',
            'categories',
            'existingGroups'
        ));
    }
}