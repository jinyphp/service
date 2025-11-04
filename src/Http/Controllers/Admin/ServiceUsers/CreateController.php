<?php

namespace Jiny\Service\Http\Controllers\Admin\ServiceUsers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\Service;
use Jiny\Service\Models\ServicePlan;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        // 폼에 필요한 데이터
        $services = Service::where('enable', true)->orderBy('title')->get();

        $statusOptions = [
            'pending' => '대기',
            'active' => '활성',
            'suspended' => '일시정지',
            'cancelled' => '취소',
            'expired' => '만료'
        ];

        $billingCycleOptions = [
            'monthly' => '월간',
            'quarterly' => '분기',
            'yearly' => '연간',
            'lifetime' => '평생'
        ];

        $paymentStatusOptions = [
            'pending' => '결제 대기',
            'paid' => '결제 완료',
            'failed' => '결제 실패'
        ];

        $paymentMethodOptions = [
            'credit_card' => '신용카드',
            'bank_transfer' => '계좌이체',
            'paypal' => 'PayPal',
            'stripe' => 'Stripe',
            'manual' => '수동 결제'
        ];

        // 사용자 샤드 옵션 (예시)
        $userShardOptions = [];
        for ($i = 1; $i <= 10; $i++) {
            $shard = sprintf('user_%03d', $i);
            $userShardOptions[$shard] = $shard;
        }

        return view('jiny-service::admin.service_users.create', compact(
            'services',
            'statusOptions',
            'billingCycleOptions',
            'paymentStatusOptions',
            'paymentMethodOptions',
            'userShardOptions'
        ));
    }
}