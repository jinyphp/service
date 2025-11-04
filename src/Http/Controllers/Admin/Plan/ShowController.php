<?php

namespace Jiny\Service\Http\Controllers\Admin\Plan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\ServicePlan;
use Jiny\Service\Models\ServicePlanDetail;
use Jiny\Service\Models\ServicePrice;

class ShowController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $plan = ServicePlan::with('service')->findOrFail($id);

        // 현재 플랜의 구독자 수
        $subscribersCount = $plan->serviceUsers()->count();

        // 활성 구독자 수
        $activeSubscribers = $plan->serviceUsers()
                                 ->where('status', 'active')
                                 ->count();

        // 이번 달 신규 구독자 수
        $newSubscribersThisMonth = $plan->serviceUsers()
                                       ->whereMonth('created_at', now()->month)
                                       ->whereYear('created_at', now()->year)
                                       ->count();

        // 최근 구독자 목록 (최근 5명)
        $recentSubscribers = $plan->serviceUsers()
                                 ->with('user')
                                 ->orderBy('created_at', 'desc')
                                 ->limit(5)
                                 ->get();

        // 월별 구독자 통계 (최근 6개월)
        $monthlyStats = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = $plan->serviceUsers()
                         ->whereMonth('created_at', $date->month)
                         ->whereYear('created_at', $date->year)
                         ->count();

            $monthlyStats[] = [
                'month' => $date->format('Y-m'),
                'month_name' => $date->format('M Y'),
                'count' => $count
            ];
        }

        // 업그레이드/다운그레이드 가능한 플랜 목록
        $upgradePlans = collect();
        $downgradePlans = collect();

        if ($plan->upgrade_paths) {
            $upgradePlans = ServicePlan::whereIn('plan_code', $plan->upgrade_paths)
                                     ->where('is_active', true)
                                     ->select('id', 'plan_name', 'plan_code', 'monthly_price')
                                     ->orderBy('monthly_price')
                                     ->get();
        }

        if ($plan->downgrade_paths) {
            $downgradePlans = ServicePlan::whereIn('plan_code', $plan->downgrade_paths)
                                        ->where('is_active', true)
                                        ->select('id', 'plan_name', 'plan_code', 'monthly_price')
                                        ->orderBy('monthly_price')
                                        ->get();
        }

        // 이번 달 수익 (결제 테이블이 없으므로 예상 수익으로 계산)
        $thisMonthRevenue = $plan->serviceUsers()
                                ->where('status', 'active')
                                ->whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)
                                ->count() * ($plan->monthly_price ?? 0);

        // 플랜 상세 정보 (service_plan_detail)
        $planDetails = ServicePlanDetail::where('service_plan_id', $plan->id)
                                      ->where('enable', true)
                                      ->orderBy('group_order')
                                      ->orderBy('pos')
                                      ->get();

        // 플랜 가격 옵션 (service_plan_price)
        $planPrices = ServicePrice::where('service_plan_id', $plan->id)
                                 ->where('enable', true)
                                 ->orderBy('pos')
                                 ->get();

        return view('jiny-service::admin.plan.show', compact(
            'plan',
            'subscribersCount',
            'activeSubscribers',
            'newSubscribersThisMonth',
            'recentSubscribers',
            'monthlyStats',
            'upgradePlans',
            'downgradePlans',
            'thisMonthRevenue',
            'planDetails',
            'planPrices'
        ));
    }
}