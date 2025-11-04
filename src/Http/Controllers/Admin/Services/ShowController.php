<?php

namespace Jiny\Service\Http\Controllers\Admin\Services;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Services 상세보기 컨트롤러
 */
class ShowController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->config = [
            'table' => 'services',
            'view' => 'jiny-service::admin.services.show',
            'title' => 'Service 상세보기',
        ];
    }

    public function __invoke(Request $request, $id)
    {
        // Eloquent 모델 사용으로 변경
        $service = \Jiny\Service\Models\SiteService::findOrFail($id);

        return view($this->config['view'], [
            'service' => $service,
            'config' => $this->config,
        ]);
    }
}