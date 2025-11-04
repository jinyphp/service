<?php

namespace Jiny\Service\Http\Controllers\Admin\Categories;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Service Categories 생성 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/service/categories/create') → CreateController::__invoke()
 */
class CreateController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->loadConfig();
    }

    protected function loadConfig()
    {
        $this->config = [
            'table' => 'service_categories',
            'view' => 'jiny-service::admin.categories.create',
            'title' => 'Service Category 생성',
            'subtitle' => '새로운 서비스 카테고리를 생성합니다.',
        ];
    }

    public function __invoke(Request $request)
    {
        $parentCategories = $this->getParentCategories();

        return view($this->config['view'], [
            'parentCategories' => $parentCategories,
            'config' => $this->config,
        ]);
    }

    protected function getParentCategories()
    {
        return DB::table('service_categories')
            ->whereNull('parent_id')
            ->where('enable', true)
            ->orderBy('pos')
            ->orderBy('title')
            ->get();
    }
}