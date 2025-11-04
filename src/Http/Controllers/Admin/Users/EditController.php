<?php

namespace Jiny\Service\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\ServiceUser;
use Jiny\Service\Models\SiteService;
use Jiny\Service\Models\ServicePlan;

class EditController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $serviceUser = ServiceUser::with(['service', 'payments', 'subscriptionLogs'])
                                 ->findOrFail($id);

        // 활성화된 서비스 목록
        $services = SiteService::where('is_active', true)
                              ->orderBy('name')
                              ->get();

        // 활성화된 플랜 목록
        $plans = ServicePlan::with('service')
                           ->where('is_active', true)
                           ->orderBy('sort_order')
                           ->orderBy('monthly_price')
                           ->get()
                           ->groupBy('service_id');

        // 상태 옵션
        $statusOptions = [
            'active' => 'Active',
            'pending' => 'Pending',
            'expired' => 'Expired',
            'cancelled' => 'Cancelled',
            'suspended' => 'Suspended'
        ];

        // 결제 주기 옵션
        $billingCycles = [
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'yearly' => 'Yearly',
            'lifetime' => 'Lifetime'
        ];

        // 결제 방법 옵션
        $paymentMethods = [
            'card' => 'Credit Card',
            'bank_transfer' => 'Bank Transfer',
            'paypal' => 'PayPal',
            'stripe' => 'Stripe',
            'manual' => 'Manual'
        ];

        // 사용자 샤드 목록 (예시)
        $userShards = [];
        for ($i = 1; $i <= 10; $i++) {
            $shardName = 'users_' . str_pad($i, 3, '0', STR_PAD_LEFT);
            $userShards[$shardName] = $shardName;
        }

        // 결제 내역
        $payments = $serviceUser->payments()
                               ->orderBy('created_at', 'desc')
                               ->limit(10)
                               ->get();

        // 구독 로그
        $subscriptionLogs = $serviceUser->subscriptionLogs()
                                       ->orderBy('created_at', 'desc')
                                       ->limit(20)
                                       ->get();

        // 통계 정보
        $stats = [
            'total_payments' => $serviceUser->payments()->count(),
            'total_paid' => $serviceUser->total_paid,
            'successful_payments' => $serviceUser->payments()->completed()->count(),
            'failed_payments' => $serviceUser->payments()->failed()->count(),
            'refunded_amount' => $serviceUser->refund_amount,
            'days_until_expiry' => $serviceUser->days_until_expiry,
        ];

        // 현재 플랜 정보
        $currentPlan = ServicePlan::where('plan_name', $serviceUser->plan_name)
                                 ->where('service_id', $serviceUser->service_id)
                                 ->first();

        // 업그레이드/다운그레이드 가능한 플랜들
        $availableUpgrades = [];
        $availableDowngrades = [];

        if ($currentPlan) {
            if ($currentPlan->upgrade_paths) {
                $availableUpgrades = ServicePlan::whereIn('plan_code', $currentPlan->upgrade_paths)
                                               ->where('service_id', $serviceUser->service_id)
                                               ->where('is_active', true)
                                               ->get();
            }

            if ($currentPlan->downgrade_paths) {
                $availableDowngrades = ServicePlan::whereIn('plan_code', $currentPlan->downgrade_paths)
                                                 ->where('service_id', $serviceUser->service_id)
                                                 ->where('is_active', true)
                                                 ->get();
            }
        }

        return view('jiny-service::admin.users.edit', compact(
            'serviceUser',
            'services',
            'plans',
            'statusOptions',
            'billingCycles',
            'paymentMethods',
            'userShards',
            'payments',
            'subscriptionLogs',
            'stats',
            'currentPlan',
            'availableUpgrades',
            'availableDowngrades'
        ));
    }
}