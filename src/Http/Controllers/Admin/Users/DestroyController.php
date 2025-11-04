<?php

namespace Jiny\Service\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\ServiceUser;

class DestroyController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $serviceUser = ServiceUser::with(['payments', 'subscriptionLogs'])->findOrFail($id);

        // 안전성 검사: 결제 내역이 있는 경우 삭제 제한
        $paymentsCount = $serviceUser->payments()->count();
        $totalPaid = $serviceUser->total_paid;

        if ($paymentsCount > 0 || $totalPaid > 0) {
            return redirect()
                ->route('admin.service.users.index')
                ->with('error', "이 구독 사용자는 결제 내역이 있어 삭제할 수 없습니다. 대신 상태를 '취소'로 변경하는 것을 권장합니다.");
        }

        // 활성 구독인 경우 경고
        if ($serviceUser->status === 'active' && $serviceUser->expires_at > now()) {
            return redirect()
                ->route('admin.service.users.index')
                ->with('error', "활성 구독 상태인 사용자는 삭제할 수 없습니다. 먼저 구독을 취소해주세요.");
        }

        // 미결제 상태나 테스트 계정인 경우만 삭제 허용
        if ($serviceUser->status === 'pending' || $serviceUser->total_paid == 0) {

            // 삭제 로그 기록
            $serviceUser->subscriptionLogs()->create([
                'user_uuid' => $serviceUser->user_uuid,
                'service_id' => $serviceUser->service_id,
                'action' => 'admin_delete',
                'action_title' => '관리자 계정 삭제',
                'action_description' => '관리자가 구독 계정을 삭제했습니다.',
                'status_before' => $serviceUser->status,
                'status_after' => 'deleted',
                'plan_before' => $serviceUser->plan_name,
                'processed_by' => 'admin',
                'processor_name' => auth()->user()->name ?? 'Unknown Admin',
                'result' => 'success',
                'action_data' => [
                    'delete_reason' => '관리자에 의한 삭제',
                    'deleted_at' => now(),
                ]
            ]);

            $userName = $serviceUser->user_name;
            $userEmail = $serviceUser->user_email;

            // Soft Delete 실행
            $serviceUser->delete();

            return redirect()
                ->route('admin.service.users.index')
                ->with('success', "구독 사용자 '{$userName} ({$userEmail})'이(가) 성공적으로 삭제되었습니다.");
        }

        return redirect()
            ->route('admin.service.users.index')
            ->with('error', "이 구독 사용자는 삭제할 수 없습니다. 상태를 확인해주세요.");
    }
}