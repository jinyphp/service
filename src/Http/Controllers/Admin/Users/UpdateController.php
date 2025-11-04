<?php

namespace Jiny\Service\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\ServiceUser;
use Jiny\Service\Models\ServicePlan;
use Carbon\Carbon;

class UpdateController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $serviceUser = ServiceUser::findOrFail($id);

        $request->validate([
            'user_uuid' => 'required|string|max:255|unique:site_service_users,user_uuid,' . $serviceUser->id,
            'user_email' => 'required|email|max:255',
            'user_name' => 'required|string|max:255',
            'user_shard' => 'nullable|string|max:255',
            'user_id' => 'nullable|integer',
            'service_id' => 'required|exists:site_services,id',
            'plan_name' => 'required|string',
            'billing_cycle' => 'required|in:monthly,quarterly,yearly,lifetime',
            'status' => 'required|in:active,pending,expired,cancelled,suspended',
            'payment_method' => 'nullable|string|max:50',
            'payment_status' => 'nullable|in:completed,pending,failed,cancelled',
            'started_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:started_at',
            'next_billing_at' => 'nullable|date',
            'plan_price' => 'nullable|numeric|min:0',
            'monthly_price' => 'nullable|numeric|min:0',
            'total_paid' => 'nullable|numeric|min:0',
            'refund_amount' => 'nullable|numeric|min:0',
            'auto_renewal' => 'boolean',
            'auto_upgrade' => 'boolean',
            'admin_notes' => 'nullable|string',
        ]);

        // 변경사항 추적을 위한 원본 데이터 백업
        $originalData = $serviceUser->toArray();

        // 플랜 정보 확인
        $plan = ServicePlan::where('plan_name', $request->plan_name)
                          ->where('service_id', $request->service_id)
                          ->first();

        if (!$plan) {
            return back()->withErrors(['plan_name' => '선택한 서비스에서 해당 플랜을 찾을 수 없습니다.']);
        }

        $data = $request->only([
            'user_uuid', 'user_email', 'user_name', 'user_shard', 'user_id',
            'service_id', 'plan_name', 'billing_cycle', 'status',
            'payment_method', 'payment_status', 'admin_notes',
            'plan_price', 'monthly_price', 'total_paid', 'refund_amount'
        ]);

        // 서비스 정보 자동 입력
        $data['service_title'] = $plan->service->name;

        // 플랜 피처 업데이트
        $data['plan_features'] = $plan->features;

        // 날짜 처리
        if ($request->started_at) {
            $data['started_at'] = Carbon::parse($request->started_at);
        }

        if ($request->expires_at) {
            $data['expires_at'] = Carbon::parse($request->expires_at);
        }

        if ($request->next_billing_at) {
            $data['next_billing_at'] = Carbon::parse($request->next_billing_at);
        }

        // Boolean 필드 처리
        $data['auto_renewal'] = $request->has('auto_renewal');
        $data['auto_upgrade'] = $request->has('auto_upgrade');

        // 취소 관련 필드 처리
        if ($data['status'] === 'cancelled' && $originalData['status'] !== 'cancelled') {
            $data['cancelled_at'] = now();
            $data['cancel_reason'] = '관리자에 의한 취소';
        } elseif ($data['status'] !== 'cancelled' && $originalData['status'] === 'cancelled') {
            $data['cancelled_at'] = null;
            $data['cancel_reason'] = null;
        }

        // 환불 관련 필드 처리
        if ($data['refund_amount'] > $originalData['refund_amount']) {
            $data['refunded_at'] = now();
        }

        $serviceUser->update($data);

        // 변경사항 로그 기록
        $changes = [];
        foreach ($data as $key => $value) {
            if (isset($originalData[$key]) && $originalData[$key] != $value) {
                $changes[$key] = [
                    'from' => $originalData[$key],
                    'to' => $value
                ];
            }
        }

        if (!empty($changes)) {
            $serviceUser->subscriptionLogs()->create([
                'user_uuid' => $data['user_uuid'],
                'service_id' => $data['service_id'],
                'action' => 'admin_update',
                'action_title' => '관리자 정보 수정',
                'action_description' => '관리자가 구독 정보를 수정했습니다.',
                'status_before' => $originalData['status'],
                'status_after' => $data['status'],
                'plan_before' => $originalData['plan_name'],
                'plan_after' => $data['plan_name'],
                'expires_before' => $originalData['expires_at'] ? Carbon::parse($originalData['expires_at']) : null,
                'expires_after' => $data['expires_at'] ? Carbon::parse($data['expires_at']) : null,
                'processed_by' => 'admin',
                'processor_name' => auth()->user()->name ?? 'Unknown Admin',
                'result' => 'success',
                'action_data' => [
                    'changes' => $changes,
                    'admin_notes' => $data['admin_notes']
                ]
            ]);
        }

        return redirect()
            ->route('admin.service.users.edit', $serviceUser->id)
            ->with('success', '서비스 구독 사용자 정보가 성공적으로 수정되었습니다.');
    }
}