<?php

namespace Jiny\Service\Http\Controllers\Admin\ServiceUsers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\ServiceUser;

class ActionController extends Controller
{
    public function activate(Request $request, $id)
    {
        try {
            $serviceUser = ServiceUser::findOrFail($id);

            if (!in_array($serviceUser->status, ['pending', 'suspended'])) {
                return redirect()->back()->with('error', '현재 상태에서는 활성화할 수 없습니다.');
            }

            $serviceUser->activate();

            return redirect()
                ->route('admin.service.service-users.show', $serviceUser->id)
                ->with('success', '구독이 활성화되었습니다.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', '활성화 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    public function suspend(Request $request, $id)
    {
        try {
            $serviceUser = ServiceUser::findOrFail($id);

            if ($serviceUser->status !== 'active') {
                return redirect()->back()->with('error', '활성 상태의 구독만 일시정지할 수 있습니다.');
            }

            $reason = $request->input('reason', '관리자에 의한 일시정지');
            $previousStatus = $serviceUser->status;

            $serviceUser->update([
                'status' => 'suspended',
                'admin_notes' => $serviceUser->admin_notes . "\n[" . now() . "] 일시정지: " . $reason
            ]);

            // 로그 기록
            $serviceUser->subscriptionLogs()->create([
                'user_uuid' => $serviceUser->user_uuid,
                'service_id' => $serviceUser->service_id,
                'action' => 'suspend',
                'action_title' => '구독 일시정지',
                'action_description' => $reason,
                'status_before' => $previousStatus,
                'status_after' => 'suspended',
                'processed_by' => 'admin',
            ]);

            return redirect()
                ->route('admin.service.service-users.show', $serviceUser->id)
                ->with('success', '구독이 일시정지되었습니다.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', '일시정지 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    public function cancel(Request $request, $id)
    {
        try {
            $serviceUser = ServiceUser::findOrFail($id);

            if (!in_array($serviceUser->status, ['active', 'suspended', 'pending'])) {
                return redirect()->back()->with('error', '현재 상태에서는 취소할 수 없습니다.');
            }

            $reason = $request->input('reason', '관리자에 의한 취소');
            $serviceUser->cancel($reason);

            return redirect()
                ->route('admin.service.service-users.show', $serviceUser->id)
                ->with('success', '구독이 취소되었습니다.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', '취소 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    public function extend(Request $request, $id)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365'
        ]);

        try {
            $serviceUser = ServiceUser::findOrFail($id);
            $days = $request->input('days');

            $serviceUser->extend($days);

            return redirect()
                ->route('admin.service.service-users.show', $serviceUser->id)
                ->with('success', "{$days}일 연장되었습니다.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', '연장 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    public function updateUserCache(Request $request, $id)
    {
        try {
            $serviceUser = ServiceUser::findOrFail($id);
            $serviceUser->updateUserCache();

            return redirect()
                ->route('admin.service.service-users.show', $serviceUser->id)
                ->with('success', '사용자 캐시 정보가 업데이트되었습니다.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', '캐시 업데이트 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}