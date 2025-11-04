<?php

namespace Jiny\Service\Http\Controllers\Admin\ServiceDetail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\SiteService;
use Jiny\Service\Models\ServicePlanDetail;

class DestroyController extends Controller
{
    public function __invoke(Request $request, $serviceId, $detailId)
    {
        $service = SiteService::findOrFail($serviceId);

        $detail = ServicePlanDetail::where('service_id', $serviceId)
                     ->findOrFail($detailId);

        $detail->delete();

        return redirect()
            ->route('admin.site.services.detail.index', $serviceId)
            ->with('success', '서비스 상세 정보가 성공적으로 삭제되었습니다.');
    }
}