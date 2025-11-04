<?php

namespace Jiny\Service\Http\Controllers\Admin\Process;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\ServiceUser;
use Jiny\Service\Models\ServicePlan;
use Jiny\Service\Models\ServicePayment;
use Jiny\Service\Models\ServiceSubscriptionLog;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SubscribeController extends Controller
{
    /**
     * 새로운 구독 생성
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_uuid' => 'required|string|max:255|unique:site_service_users,user_uuid',
            'user_email' => 'required|email|max:255',
            'user_name' => 'required|string|max:255',
            'user_shard' => 'nullable|string|max:255',
            'user_id' => 'nullable|integer',
            'service_id' => 'required|exists:site_services,id',
            'plan_code' => 'required|exists:site_service_plans,plan_code',
            'billing_cycle' => 'required|in:monthly,quarterly,yearly,lifetime',
            'payment_method' => 'nullable|string|max:50',
            'auto_renewal' => 'boolean',
            'use_trial' => 'boolean',
        ]);

        // 플랜 정보 조회
        $plan = ServicePlan::where('plan_code', $request->plan_code)
                          ->where('service_id', $request->service_id)
                          ->where('is_active', true)
                          ->first();

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => '선택한 플랜을 찾을 수 없습니다.'
            ], 404);
        }

        // 결제 주기가 해당 플랜에서 지원되는지 확인
        $cycleAvailable = match ($request->billing_cycle) {
            'monthly' => $plan->monthly_available,
            'quarterly' => $plan->quarterly_available,
            'yearly' => $plan->yearly_available,
            'lifetime' => $plan->lifetime_available,
        };

        if (!$cycleAvailable) {
            return response()->json([
                'success' => false,
                'message' => '선택한 결제 주기는 이 플랜에서 지원되지 않습니다.'
            ], 400);
        }

        try {
            \DB::transaction(function () use ($request, $plan, &$serviceUser, &$payment) {

                // 구독 사용자 생성
                $startDate = now();
                $useTrialPeriod = $request->use_trial && $plan->has_trial;

                if ($useTrialPeriod) {
                    $expiresAt = $startDate->copy()->addDays($plan->trial_period_days);
                    $nextBillingAt = $expiresAt;
                } else {
                    $expiresAt = match ($request->billing_cycle) {
                        'monthly' => $startDate->copy()->addMonth(),
                        'quarterly' => $startDate->copy()->addMonths(3),
                        'yearly' => $startDate->copy()->addYear(),
                        'lifetime' => $startDate->copy()->addYears(100),
                    };
                    $nextBillingAt = $request->billing_cycle !== 'lifetime' ? $expiresAt : null;
                }

                $serviceUser = ServiceUser::create([
                    'user_uuid' => $request->user_uuid,
                    'user_email' => $request->user_email,
                    'user_name' => $request->user_name,
                    'user_shard' => $request->user_shard,
                    'user_id' => $request->user_id,
                    'service_id' => $request->service_id,
                    'service_title' => $plan->service->name,
                    'status' => $useTrialPeriod ? 'trial' : 'active',
                    'billing_cycle' => $request->billing_cycle,
                    'started_at' => $startDate,
                    'expires_at' => $expiresAt,
                    'next_billing_at' => $nextBillingAt,
                    'plan_name' => $plan->plan_name,
                    'plan_price' => $plan->calculatePrice($request->billing_cycle),
                    'plan_features' => $plan->features,
                    'monthly_price' => $plan->monthly_price,
                    'payment_method' => $request->payment_method,
                    'payment_status' => $useTrialPeriod ? 'trial' : 'pending',
                    'auto_renewal' => $request->has('auto_renewal'),
                    'auto_upgrade' => false,
                    'total_paid' => 0,
                    'refund_amount' => 0,
                ]);

                // 유료 플랜인 경우 결제 레코드 생성
                if (!$useTrialPeriod && $plan->calculatePrice($request->billing_cycle) > 0) {
                    $payment = ServicePayment::create([
                        'service_user_id' => $serviceUser->id,
                        'user_uuid' => $request->user_uuid,
                        'service_id' => $request->service_id,
                        'order_id' => 'SUB-' . $serviceUser->id . '-' . time(),
                        'amount' => $plan->calculatePrice($request->billing_cycle),
                        'tax_amount' => 0,
                        'discount_amount' => 0,
                        'final_amount' => $plan->calculatePrice($request->billing_cycle),
                        'currency' => 'KRW',
                        'payment_method' => $request->payment_method,
                        'payment_provider' => 'manual',
                        'status' => 'pending',
                        'payment_type' => 'subscription',
                        'billing_cycle' => $request->billing_cycle,
                        'billing_period_start' => $startDate,
                        'billing_period_end' => $expiresAt,
                        'due_date' => $startDate->copy()->addDays(7),
                        'retry_count' => 0,
                    ]);
                }

                // 구독 로그 기록
                ServiceSubscriptionLog::logSubscribe(
                    $serviceUser->id,
                    $plan->plan_name,
                    $expiresAt
                );
            });

            return response()->json([
                'success' => true,
                'message' => '구독이 성공적으로 생성되었습니다.',
                'data' => [
                    'service_user_id' => $serviceUser->id,
                    'user_uuid' => $serviceUser->user_uuid,
                    'plan_name' => $serviceUser->plan_name,
                    'status' => $serviceUser->status,
                    'expires_at' => $serviceUser->expires_at,
                    'payment_id' => $payment->id ?? null,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '구독 생성 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }
}