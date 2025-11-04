<?php

namespace Jiny\Service\Http\Controllers\Admin\ServiceUsers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\ServiceUser;
use Jiny\Service\Models\Service;

class EditController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $serviceUser = ServiceUser::with(['service'])->findOrFail($id);

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
            'failed' => '결제 실패',
            'refunded' => '환불 완료'
        ];

        $paymentMethodOptions = [
            'credit_card' => '신용카드',
            'bank_transfer' => '계좌이체',
            'paypal' => 'PayPal',
            'stripe' => 'Stripe',
            'manual' => '수동 결제'
        ];

        // 사용자 샤드 옵션
        $userShardOptions = [];
        for ($i = 1; $i <= 10; $i++) {
            $shard = sprintf('user_%03d', $i);
            $userShardOptions[$shard] = $shard;
        }

        // 변경 가능 여부 체크
        $canEdit = [
            'user_info' => true, // 사용자 정보는 항상 수정 가능
            'service' => $serviceUser->status !== 'active', // 활성 상태에서는 서비스 변경 불가
            'billing' => in_array($serviceUser->status, ['pending', 'active']), // 결제 정보 수정 가능 상태
            'dates' => true, // 날짜는 항상 수정 가능 (관리자)
            'status' => true, // 상태는 항상 변경 가능
        ];

        return view('jiny-service::admin.service_users.edit', compact(
            'serviceUser',
            'services',
            'statusOptions',
            'billingCycleOptions',
            'paymentStatusOptions',
            'paymentMethodOptions',
            'userShardOptions',
            'canEdit'
        ));
    }
}