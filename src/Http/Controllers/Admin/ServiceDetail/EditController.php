<?php

namespace Jiny\Service\Http\Controllers\Admin\ServiceDetail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\SiteService;
use Jiny\Service\Models\ServicePlanDetail;

class EditController extends Controller
{
    public function __invoke(Request $request, $serviceId, $detailId)
    {
        $service = SiteService::findOrFail($serviceId);

        $detail = ServicePlanDetail::where('service_id', $serviceId)
                     ->findOrFail($detailId);

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

        return view('jiny-service::admin.service_detail.edit', compact(
            'service',
            'detail',
            'detailTypes',
            'valueTypes',
            'categories',
            'existingGroups'
        ));
    }
}