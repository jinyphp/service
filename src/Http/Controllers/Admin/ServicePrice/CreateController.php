<?php

namespace Jiny\Service\Http\Controllers\Admin\ServicePrice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\SiteService;

class CreateController extends Controller
{
    public function __invoke(Request $request, $serviceId)
    {
        $service = SiteService::findOrFail($serviceId);

        return view('jiny-service::admin.service_price.create', compact('service'));
    }
}