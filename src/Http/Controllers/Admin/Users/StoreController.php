<?php

namespace Jiny\Service\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\ServiceUser;
use Jiny\Service\Models\ServicePlan;
use Illuminate\Support\Str;
use Carbon\Carbon;

class StoreController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'user_uuid' => 'required|string|max:255|unique:site_service_users,user_uuid',
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
            'plan_price' => 'nullable|numeric|min:0',
            'monthly_price' => 'nullable|numeric|min:0',
            'auto_renewal' => 'boolean',
            'auto_upgrade' => 'boolean',
            'admin_notes' => 'nullable|string',
        ]);

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
            'payment_method', 'payment_status', 'admin_notes'
        ]);

        // 서비스 정보 자동 입력
        $data['service_title'] = $plan->service->name;

        // 플랜 정보 설정
        $data['plan_features'] = $plan->features;
        $data['plan_price'] = $request->plan_price ?: $plan->calculatePrice($request->billing_cycle);
        $data['monthly_price'] = $plan->monthly_price;

        // 날짜 설정
        $data['started_at'] = $request->started_at ? Carbon::parse($request->started_at) : now();

        if ($request->expires_at) {
            $data['expires_at'] = Carbon::parse($request->expires_at);
        } else {
            // 기본 만료일 계산
            $startDate = Carbon::parse($data['started_at']);
            $data['expires_at'] = match ($request->billing_cycle) {
                'monthly' => $startDate->addMonth(),
                'quarterly' => $startDate->addMonths(3),
                'yearly' => $startDate->addYear(),
                'lifetime' => $startDate->addYears(100),
                default => $startDate->addMonth(),
            };
        }

        // 다음 결제일 설정
        if ($request->billing_cycle !== 'lifetime' && $data['status'] === 'active') {
            $data['next_billing_at'] = $data['expires_at'];
        }

        // Boolean 필드 처리
        $data['auto_renewal'] = $request->has('auto_renewal');
        $data['auto_upgrade'] = $request->has('auto_upgrade');

        // 기본값 설정
        $data['total_paid'] = 0;
        $data['refund_amount'] = 0;

        $serviceUser = ServiceUser::create($data);

        // 구독 로그 기록
        $serviceUser->subscriptionLogs()->create([
            'user_uuid' => $data['user_uuid'],
            'service_id' => $data['service_id'],
            'action' => 'manual_create',
            'action_title' => '관리자 수동 생성',
            'action_description' => '관리자가 수동으로 구독을 생성했습니다.',
            'status_after' => $data['status'],
            'plan_after' => $data['plan_name'],
            'expires_after' => $data['expires_at'],
            'processed_by' => 'admin',
            'processor_name' => auth()->user()->name ?? 'Unknown Admin',
            'result' => 'success',
        ]);

        return redirect()
            ->route('admin.service.users.index')
            ->with('success', '서비스 구독 사용자가 성공적으로 생성되었습니다.');
    }
}