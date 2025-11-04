<?php

namespace Jiny\Service\Http\Controllers\Admin\ServicePrice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\SiteService;
use Jiny\Service\Models\ServicePrice;

class IndexController extends Controller
{
    public function __invoke(Request $request, $serviceId)
    {
        $service = SiteService::findOrFail($serviceId);

        $prices = ServicePrice::where('service_id', $serviceId)
                    ->when($request->search, function ($query, $search) {
                        $query->where(function ($q) use ($search) {
                            $q->where('name', 'like', '%' . $search . '%')
                              ->orWhere('code', 'like', '%' . $search . '%')
                              ->orWhere('description', 'like', '%' . $search . '%');
                        });
                    })
                    ->when($request->status !== null, function ($query) use ($request) {
                        $query->where('enable', $request->status === 'active');
                    })
                    ->when($request->type, function ($query, $type) {
                        switch ($type) {
                            case 'popular':
                                $query->where('is_popular', true);
                                break;
                            case 'recommended':
                                $query->where('is_recommended', true);
                                break;
                        }
                    })
                    ->orderBy('pos', 'asc')
                    ->orderBy('price', 'asc')
                    ->paginate(15);

        // 통계 계산
        $stats = [
            'total' => ServicePrice::where('service_id', $serviceId)->count(),
            'active' => ServicePrice::where('service_id', $serviceId)->where('enable', true)->count(),
            'popular' => ServicePrice::where('service_id', $serviceId)->where('is_popular', true)->count(),
            'with_trial' => ServicePrice::where('service_id', $serviceId)->where('has_trial', true)->count(),
            'with_discount' => ServicePrice::where('service_id', $serviceId)->whereNotNull('sale_price')->count(),
        ];

        return view('jiny-service::admin.service_price.index', compact(
            'service',
            'prices',
            'stats'
        ));
    }
}