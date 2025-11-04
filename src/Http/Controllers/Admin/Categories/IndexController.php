<?php

namespace Jiny\Service\Http\Controllers\Admin\Categories;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Service Categories 목록 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/service/categories/') → IndexController::__invoke()
 */
class IndexController extends Controller
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
            'view' => 'jiny-service::admin.categories.index',
            'title' => 'Service Categories 관리',
            'subtitle' => '서비스 카테고리를 관리합니다.',
            'per_page' => 15,
        ];
    }

    public function __invoke(Request $request)
    {
        $query = $this->buildQuery();
        $query = $this->applyFilters($query, $request);

        $categories = $query->orderBy('pos')
            ->orderBy('title')
            ->paginate($this->config['per_page'])
            ->withQueryString();

        $stats = $this->getStatistics();

        return view($this->config['view'], [
            'categories' => $categories,
            'stats' => $stats,
            'config' => $this->config,
        ]);
    }

    protected function buildQuery()
    {
        return DB::table($this->config['table'])
            ->leftJoin('service_categories as parent', 'service_categories.parent_id', '=', 'parent.id')
            ->leftJoin(
                DB::raw('(SELECT category_id, COUNT(*) as service_count FROM services WHERE deleted_at IS NULL GROUP BY category_id) as service_counts'),
                'service_categories.id',
                '=',
                'service_counts.category_id'
            )
            ->select(
                'service_categories.*',
                'parent.title as parent_name',
                DB::raw('COALESCE(service_counts.service_count, 0) as service_count')
            );
    }

    protected function applyFilters($query, Request $request)
    {
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('service_categories.title', 'like', "%{$search}%")
                  ->orWhere('service_categories.description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('enable') && $request->get('enable') !== 'all') {
            $query->where('service_categories.enable', $request->get('enable') === '1');
        }

        if ($request->filled('parent') && $request->get('parent') !== 'all') {
            if ($request->get('parent') === '0') {
                $query->whereNull('service_categories.parent_id');
            } else {
                $query->where('service_categories.parent_id', $request->get('parent'));
            }
        }

        return $query;
    }

    protected function getStatistics()
    {
        $table = $this->config['table'];

        return [
            'total' => DB::table($table)->count(),
            'enabled' => DB::table($table)->where('enable', true)->count(),
            'disabled' => DB::table($table)->where('enable', false)->count(),
            'parent_categories' => DB::table($table)->whereNull('parent_id')->count(),
            'sub_categories' => DB::table($table)->whereNotNull('parent_id')->count(),
        ];
    }

    public function getParentCategories()
    {
        return DB::table('service_categories')
            ->whereNull('parent_id')
            ->where('enable', true)
            ->orderBy('pos')
            ->orderBy('title')
            ->get();
    }
}