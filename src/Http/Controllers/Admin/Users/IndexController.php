<?php

namespace Jiny\Service\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\ServiceUser;
use Jiny\Service\Models\SiteService;
use Jiny\Service\Models\ServicePlan;

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        // 필터링을 위한 기본 데이터
        $services = SiteService::select('id', 'name')->orderBy('name')->get();
        $plans = ServicePlan::select('plan_name')->distinct()->orderBy('plan_name')->get();

        // 구독 사용자 목록 조회 with 필터링
        $query = ServiceUser::with(['service'])
                    ->orderBy('created_at', 'desc');

        // 서비스별 필터링
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        // 플랜별 필터링
        if ($request->filled('plan_name')) {
            $query->where('plan_name', $request->plan_name);
        }

        // 상태별 필터링
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'expired') {
                $query->expired();
            } elseif ($request->status === 'expiring_soon') {
                $query->expiringSoon(7);
            } else {
                $query->where('status', $request->status);
            }
        }

        // 결제 상태별 필터링
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // 결제 주기별 필터링
        if ($request->filled('billing_cycle')) {
            $query->where('billing_cycle', $request->billing_cycle);
        }

        // 자동 갱신 필터링
        if ($request->filled('auto_renewal')) {
            $query->where('auto_renewal', $request->auto_renewal === 'true');
        }

        // 검색
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('user_uuid', 'like', "%{$search}%")
                  ->orWhere('user_email', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%")
                  ->orWhere('plan_name', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20)->withQueryString();

        // 통계 정보
        $stats = [
            'total' => ServiceUser::count(),
            'active' => ServiceUser::active()->count(),
            'expired' => ServiceUser::expired()->count(),
            'expiring_soon' => ServiceUser::expiringSoon(7)->count(),
            'total_revenue' => ServiceUser::sum('total_paid'),
            'monthly_revenue' => ServiceUser::where('created_at', '>=', now()->startOfMonth())->sum('total_paid'),
        ];

        // 최근 활동
        $recentActivities = ServiceUser::with('service')
                                     ->orderBy('updated_at', 'desc')
                                     ->limit(10)
                                     ->get();

        return view('jiny-service::admin.users.index', compact(
            'users',
            'services',
            'plans',
            'stats',
            'recentActivities'
        ));
    }
}