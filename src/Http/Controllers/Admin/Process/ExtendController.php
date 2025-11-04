<?php

namespace Jiny\Service\Http\Controllers\Admin\Process;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\ServiceUser;
use Jiny\Service\Models\ServicePayment;
use Jiny\Service\Models\ServiceSubscriptionLog;
use Carbon\Carbon;

class ExtendController extends Controller
{
    /**
     * 구독 기간 연장
     */
    public function extend(Request $request, $serviceUserId)
    {
        $request->validate([
            'extend_type' => 'required|in:days,billing_cycle,custom',
            'extend_days' => 'required_if:extend_type,days|integer|min:1|max:3650',
            'extend_cycles' => 'required_if:extend_type,billing_cycle|integer|min:1|max:12',
            'custom_expires_at' => 'required_if:extend_type,custom|date|after:now',
            'charge_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:50',
            'extend_reason' => 'nullable|string|max:500',
            'admin_notes' => 'nullable|string|max:1000',
            'create_payment' => 'boolean',
        ]);

        $serviceUser = ServiceUser::findOrFail($serviceUserId);

        try {
            \DB::transaction(function () use ($request, $serviceUser, &$payment) {
                $originalExpiresAt = $serviceUser->expires_at;

                // 새로운 만료일 계산
                $newExpiresAt = $this->calculateNewExpiresAt($request, $serviceUser);

                // 다음 결제일 계산
                $newNextBillingAt = null;
                if ($serviceUser->billing_cycle !== 'lifetime' && $serviceUser->auto_renewal) {
                    $newNextBillingAt = $newExpiresAt;
                }

                // 구독 사용자 정보 업데이트
                $serviceUser->update([
                    'expires_at' => $newExpiresAt,
                    'next_billing_at' => $newNextBillingAt,
                    'status' => 'active', // 만료된 구독도 연장하면 활성화
                ]);

                // 결제 레코드 생성 (옵션)
                if ($request->create_payment && $request->charge_amount > 0) {
                    $payment = ServicePayment::create([
                        'service_user_id' => $serviceUser->id,
                        'user_uuid' => $serviceUser->user_uuid,
                        'service_id' => $serviceUser->service_id,
                        'order_id' => 'EXT-' . $serviceUser->id . '-' . time(),
                        'amount' => $request->charge_amount,
                        'tax_amount' => 0,
                        'discount_amount' => 0,
                        'final_amount' => $request->charge_amount,
                        'currency' => 'KRW',
                        'payment_method' => $request->payment_method ?: 'manual',
                        'payment_provider' => 'manual',
                        'status' => 'completed',
                        'payment_type' => 'extension',
                        'billing_cycle' => $serviceUser->billing_cycle,
                        'billing_period_start' => $originalExpiresAt,
                        'billing_period_end' => $newExpiresAt,
                        'paid_at' => now(),
                    ]);

                    // 결제 완료 시 총 결제 금액 업데이트
                    $serviceUser->increment('total_paid', $request->charge_amount);
                }

                // 연장 로그 기록
                $extensionDays = $originalExpiresAt->diffInDays($newExpiresAt);
                ServiceSubscriptionLog::logAdminAction(
                    $serviceUser->id,
                    '구독 기간 연장',
                    $request->extend_reason ?: "구독이 {$extensionDays}일 연장되었습니다.",
                    auth()->id(),
                    auth()->user()->name ?? 'Unknown Admin'
                );

                // 갱신 로그 (결제가 있는 경우)
                if ($payment) {
                    ServiceSubscriptionLog::logRenew(
                        $serviceUser->id,
                        $request->charge_amount,
                        $newExpiresAt
                    );
                }
            });

            $extensionDays = $serviceUser->expires_at->diffInDays(Carbon::parse($serviceUser->getOriginal('expires_at')));

            return response()->json([
                'success' => true,
                'message' => "구독이 {$extensionDays}일 연장되었습니다.",
                'data' => [
                    'service_user_id' => $serviceUser->id,
                    'old_expires_at' => $serviceUser->getOriginal('expires_at'),
                    'new_expires_at' => $serviceUser->expires_at,
                    'extension_days' => $extensionDays,
                    'payment_id' => $payment->id ?? null,
                    'charge_amount' => $request->charge_amount ?? 0,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '구독 연장 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 구독 갱신 (자동/수동)
     */
    public function renew(Request $request, $serviceUserId)
    {
        $request->validate([
            'payment_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|max:50',
            'transaction_id' => 'nullable|string|max:255',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $serviceUser = ServiceUser::findOrFail($serviceUserId);

        try {
            \DB::transaction(function () use ($request, $serviceUser, &$payment) {
                $originalExpiresAt = $serviceUser->expires_at;

                // 새로운 만료일 계산 (현재 만료일 기준으로 결제 주기만큼 연장)
                $newExpiresAt = match ($serviceUser->billing_cycle) {
                    'monthly' => $originalExpiresAt->copy()->addMonth(),
                    'quarterly' => $originalExpiresAt->copy()->addMonths(3),
                    'yearly' => $originalExpiresAt->copy()->addYear(),
                    default => $originalExpiresAt->copy()->addMonth(),
                };

                // 다음 결제일 설정
                $newNextBillingAt = $serviceUser->billing_cycle !== 'lifetime' && $serviceUser->auto_renewal
                    ? $newExpiresAt : null;

                // 구독 사용자 정보 업데이트
                $serviceUser->update([
                    'expires_at' => $newExpiresAt,
                    'next_billing_at' => $newNextBillingAt,
                    'status' => 'active',
                    'payment_status' => 'completed',
                ]);

                // 결제 레코드 생성
                $payment = ServicePayment::create([
                    'service_user_id' => $serviceUser->id,
                    'user_uuid' => $serviceUser->user_uuid,
                    'service_id' => $serviceUser->service_id,
                    'transaction_id' => $request->transaction_id,
                    'order_id' => 'REN-' . $serviceUser->id . '-' . time(),
                    'amount' => $request->payment_amount,
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                    'final_amount' => $request->payment_amount,
                    'currency' => 'KRW',
                    'payment_method' => $request->payment_method,
                    'payment_provider' => 'manual',
                    'status' => 'completed',
                    'payment_type' => 'renewal',
                    'billing_cycle' => $serviceUser->billing_cycle,
                    'billing_period_start' => $originalExpiresAt,
                    'billing_period_end' => $newExpiresAt,
                    'paid_at' => now(),
                ]);

                // 총 결제 금액 업데이트
                $serviceUser->increment('total_paid', $request->payment_amount);

                // 갱신 로그 기록
                ServiceSubscriptionLog::logRenew(
                    $serviceUser->id,
                    $request->payment_amount,
                    $newExpiresAt
                );

                // 관리자 액션 로그
                ServiceSubscriptionLog::logAdminAction(
                    $serviceUser->id,
                    '구독 갱신',
                    $request->admin_notes ?: '관리자가 구독을 갱신했습니다.',
                    auth()->id(),
                    auth()->user()->name ?? 'Unknown Admin'
                );
            });

            return response()->json([
                'success' => true,
                'message' => '구독이 성공적으로 갱신되었습니다.',
                'data' => [
                    'service_user_id' => $serviceUser->id,
                    'old_expires_at' => $serviceUser->getOriginal('expires_at'),
                    'new_expires_at' => $serviceUser->expires_at,
                    'payment_id' => $payment->id,
                    'payment_amount' => $request->payment_amount,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '구독 갱신 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 새로운 만료일 계산
     */
    private function calculateNewExpiresAt(Request $request, ServiceUser $serviceUser): Carbon
    {
        return match ($request->extend_type) {
            'days' => $serviceUser->expires_at->copy()->addDays($request->extend_days),
            'billing_cycle' => $this->extendByCycles($serviceUser, $request->extend_cycles),
            'custom' => Carbon::parse($request->custom_expires_at),
        };
    }

    /**
     * 결제 주기 기준으로 연장
     */
    private function extendByCycles(ServiceUser $serviceUser, int $cycles): Carbon
    {
        $currentExpires = $serviceUser->expires_at->copy();

        return match ($serviceUser->billing_cycle) {
            'monthly' => $currentExpires->addMonths($cycles),
            'quarterly' => $currentExpires->addMonths($cycles * 3),
            'yearly' => $currentExpires->addYears($cycles),
            default => $currentExpires->addMonths($cycles),
        };
    }
}