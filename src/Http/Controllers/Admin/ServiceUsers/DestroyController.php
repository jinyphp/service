<?php

namespace Jiny\Service\Http\Controllers\Admin\ServiceUsers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\ServiceUser;

class DestroyController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        try {
            $serviceUser = ServiceUser::findOrFail($id);

            // 활성 구독자는 바로 삭제할 수 없음
            if ($serviceUser->status === 'active') {
                return redirect()
                    ->back()
                    ->with('error', '활성 상태의 구독자는 삭제할 수 없습니다. 먼저 구독을 취소해주세요.');
            }

            // 결제가 진행 중인 경우 삭제 불가
            if ($serviceUser->payment_status === 'pending') {
                return redirect()
                    ->back()
                    ->with('error', '결제가 진행 중인 구독자는 삭제할 수 없습니다.');
            }

            // 삭제 로그 기록
            $serviceUser->subscriptionLogs()->create([
                'user_uuid' => $serviceUser->user_uuid,
                'service_id' => $serviceUser->service_id,
                'action' => 'delete',
                'action_title' => '구독 삭제',
                'action_description' => '관리자에 의해 구독이 삭제되었습니다.',
                'status_before' => $serviceUser->status,
                'status_after' => 'deleted',
                'processed_by' => 'admin',
            ]);

            // 소프트 삭제 실행
            $serviceUser->delete();

            return redirect()
                ->route('admin.service.service-users.index')
                ->with('success', '서비스 구독자가 성공적으로 삭제되었습니다.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', '구독자 삭제 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}