<?php

namespace Jiny\Service\Http\Controllers\Admin\ServicePrice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\SiteService;
use Jiny\Service\Models\ServicePrice;

class EditController extends Controller
{
    public function __invoke(Request $request, $serviceId, $priceId)
    {
        $service = SiteService::findOrFail($serviceId);

        $price = ServicePrice::where('service_id', $serviceId)
                   ->findOrFail($priceId);

        return view('jiny-service::admin.service_price.edit', compact('service', 'price'));
    }
}