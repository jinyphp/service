<?php

namespace Jiny\Service\Http\Controllers\Admin\ServiceUsers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\ServiceUser;

class ShowController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $serviceUser = ServiceUser::with(['service'])->findOrFail($id);

        // 샤딩된 사용자 정보 조회
        $shardUser = $serviceUser->getUserFromShard();

        // 결제 이력 (임시로 빈 컬렉션)
        $paymentHistory = collect(); // 실제로는 payments 관계를 사용

        // 구독 로그 (임시로 빈 컬렉션)
        $subscriptionLogs = collect(); // 실제로는 subscriptionLogs 관계를 사용

        // 통계 정보
        $stats = [
            'total_paid' => $serviceUser->total_paid,
            'days_until_expiry' => $serviceUser->days_until_expiry,
            'is_active' => $serviceUser->is_active,
            'is_expired' => $serviceUser->is_expired,
            'is_expiring_soon' => $serviceUser->is_expiring_soon,
        ];

        // 관련 서비스들 (같은 사용자의 다른 구독)
        $relatedSubscriptions = ServiceUser::where('user_uuid', $serviceUser->user_uuid)
                                          ->where('id', '!=', $serviceUser->id)
                                          ->with('service')
                                          ->orderBy('created_at', 'desc')
                                          ->take(5)
                                          ->get();

        // 상태 변경 가능 여부
        $canActivate = in_array($serviceUser->status, ['pending', 'suspended']);
        $canSuspend = $serviceUser->status === 'active';
        $canCancel = in_array($serviceUser->status, ['active', 'suspended', 'pending']);
        $canReactivate = in_array($serviceUser->status, ['cancelled', 'expired']);

        return view('jiny-service::admin.service_users.show', compact(
            'serviceUser',
            'shardUser',
            'paymentHistory',
            'subscriptionLogs',
            'stats',
            'relatedSubscriptions',
            'canActivate',
            'canSuspend',
            'canCancel',
            'canReactivate'
        ));
    }
}