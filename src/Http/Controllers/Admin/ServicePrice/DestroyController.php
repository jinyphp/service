<?php

namespace Jiny\Service\Http\Controllers\Admin\ServicePrice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\SiteService;
use Jiny\Service\Models\ServicePrice;

class DestroyController extends Controller
{
    public function __invoke(Request $request, $serviceId, $priceId)
    {
        $service = SiteService::findOrFail($serviceId);

        $price = ServicePrice::where('service_id', $serviceId)
                   ->findOrFail($priceId);

        // 다른 가격 옵션이 있는지 확인
        $otherPrices = ServicePrice::where('service_id', $serviceId)
                         ->where('id', '!=', $priceId)
                         ->count();

        if ($otherPrices === 0) {
            return redirect()
                ->route('admin.site.services.price.index', $serviceId)
                ->with('error', '최소 하나의 가격 옵션은 유지되어야 합니다.');
        }

        // 기본 옵션 삭제 시 다른 옵션을 기본으로 설정
        if ($price->is_default && $otherPrices > 0) {
            $nextPrice = ServicePrice::where('service_id', $serviceId)
                          ->where('id', '!=', $priceId)
                          ->where('enable', true)
                          ->orderBy('sort_order')
                          ->first();

            if ($nextPrice) {
                $nextPrice->update(['is_default' => true]);
            }
        }

        // 삭제 실행 (Soft Delete)
        $price->delete();

        return redirect()
            ->route('admin.site.services.price.index', $serviceId)
            ->with('success', '서비스 가격이 성공적으로 삭제되었습니다.');
    }
}