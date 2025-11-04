<?php

namespace Jiny\Service\Http\Controllers\Admin\Dashboard;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Service Dashboard 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/service/') → IndexController::__invoke()
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
            'title' => 'Service Dashboard',
            'subtitle' => '서비스 관리 대시보드입니다.',
            'view' => 'jiny-service::admin.dashboard.index',
        ];
    }

    public function __invoke(Request $request)
    {
        // 전체 통계 수집
        $stats = $this->getOverallStatistics();

        // 최근 서비스 목록
        $recentServices = $this->getRecentServices();

        // 카테고리별 통계
        $categoryStats = $this->getCategoryStatistics();

        // 월별 서비스 등록 통계 (최근 6개월)
        $monthlyStats = $this->getMonthlyStatistics();

        return view($this->config['view'], [
            'stats' => $stats,
            'recentServices' => $recentServices,
            'categoryStats' => $categoryStats,
            'monthlyStats' => $monthlyStats,
            'config' => $this->config,
        ]);
    }

    protected function getOverallStatistics()
    {
        $total = DB::table('services')->whereNull('deleted_at')->count();
        $published = DB::table('services')->where('enable', true)->whereNull('deleted_at')->count();
        $draft = DB::table('services')->where('enable', false)->whereNull('deleted_at')->count();
        $featured = DB::table('services')->where('featured', true)->whereNull('deleted_at')->count();

        // 카테고리 수
        $categories = DB::table('service_categories')->where('enable', true)->count();

        // 이번 달 신규 등록
        $thisMonth = DB::table('services')
            ->whereNull('deleted_at')
            ->whereYear('created_at', date('Y'))
            ->whereMonth('created_at', date('m'))
            ->count();

        return [
            'total' => $total,
            'published' => $published,
            'draft' => $draft,
            'featured' => $featured,
            'categories' => $categories,
            'this_month' => $thisMonth,
            'published_rate' => $total > 0 ? round(($published / $total) * 100, 1) : 0,
        ];
    }

    protected function getRecentServices($limit = 5)
    {
        return DB::table('services')
            ->leftJoin('service_categories', 'services.category_id', '=', 'service_categories.id')
            ->select(
                'services.*',
                'service_categories.title as category_name'
            )
            ->whereNull('services.deleted_at')
            ->orderBy('services.created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    protected function getCategoryStatistics()
    {
        return DB::table('service_categories')
            ->leftJoin('services', function($join) {
                $join->on('service_categories.id', '=', 'services.category_id')
                     ->whereNull('services.deleted_at');
            })
            ->select(
                'service_categories.id',
                'service_categories.title',
                DB::raw('COUNT(services.id) as service_count'),
                DB::raw('SUM(CASE WHEN services.enable = 1 THEN 1 ELSE 0 END) as published_count')
            )
            ->where('service_categories.enable', true)
            ->groupBy('service_categories.id', 'service_categories.title')
            ->orderBy('service_count', 'desc')
            ->get();
    }

    protected function getMonthlyStatistics($months = 6)
    {
        $stats = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $year = $date->year;
            $month = $date->month;

            $count = DB::table('services')
                ->whereNull('deleted_at')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count();

            $stats[] = [
                'year' => $year,
                'month' => $month,
                'month_name' => $date->format('Y-m'),
                'count' => $count,
            ];
        }

        return $stats;
    }
}