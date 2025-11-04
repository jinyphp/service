<?php

namespace Jiny\Service\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\SiteService;
use Jiny\Service\Models\ServicePlan;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
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

        return view('jiny-service::admin.users.create', compact(
            'services',
            'plans',
            'statusOptions',
            'billingCycles',
            'paymentMethods',
            'userShards'
        ));
    }
}