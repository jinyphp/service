<?php

namespace Jiny\Service\Http\Controllers\Admin\Process;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\ServiceUser;
use Jiny\Service\Models\ServiceSubscriptionLog;

class CancelController extends Controller
{
    /**
     * 구독 취소
     */
    public function cancel(Request $request, $serviceUserId)
    {
        $request->validate([
            'cancel_reason' => 'nullable|string|max:500',
            'immediate_cancel' => 'boolean',
            'refund_request' => 'boolean',
            'refund_amount' => 'nullable|numeric|min:0',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $serviceUser = ServiceUser::findOrFail($serviceUserId);

        // 이미 취소된 구독인지 확인
        if ($serviceUser->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => '이미 취소된 구독입니다.'
            ], 400);
        }

        try {
            \DB::transaction(function () use ($request, $serviceUser) {
                $originalStatus = $serviceUser->status;
                $originalExpiresAt = $serviceUser->expires_at;

                // 즉시 취소 또는 기간 만료 후 취소
                if ($request->immediate_cancel) {
                    // 즉시 취소: 상태를 취소로 변경하고 만료일을 현재로 설정
                    $serviceUser->update([
                        'status' => 'cancelled',
                        'expires_at' => now(),
                        'cancelled_at' => now(),
                        'cancel_reason' => $request->cancel_reason ?: '관리자에 의한 즉시 취소',
                        'auto_renewal' => false,
                        'next_billing_at' => null,
                    ]);
                } else {
                    // 기간 만료 후 취소: 자동 갱신만 비활성화
                    $serviceUser->update([
                        'auto_renewal' => false,
                        'cancel_reason' => $request->cancel_reason ?: '관리자에 의한 취소 예약',
                    ]);
                }

                // 환불 요청이 있는 경우
                if ($request->refund_request && $request->refund_amount > 0) {
                    $serviceUser->increment('refund_amount', $request->refund_amount);
                    $serviceUser->update(['refunded_at' => now()]);

                    // 환불 로그 기록
                    ServiceSubscriptionLog::logRefund(
                        $serviceUser->id,
                        $request->refund_amount,
                        $request->cancel_reason
                    );
                }

                // 취소 로그 기록
                ServiceSubscriptionLog::logCancel(
                    $serviceUser->id,
                    $request->cancel_reason
                );

                // 관리자 액션 로그 추가
                ServiceSubscriptionLog::logAdminAction(
                    $serviceUser->id,
                    $request->immediate_cancel ? '즉시 구독 취소' : '구독 취소 예약',
                    $request->admin_notes ?: '관리자가 구독을 취소했습니다.',
                    auth()->id(),
                    auth()->user()->name ?? 'Unknown Admin'
                );
            });

            $message = $request->immediate_cancel
                ? '구독이 즉시 취소되었습니다.'
                : '구독이 현재 기간 종료 후 취소되도록 설정되었습니다.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'service_user_id' => $serviceUser->id,
                    'status' => $serviceUser->status,
                    'expires_at' => $serviceUser->expires_at,
                    'cancelled_at' => $serviceUser->cancelled_at,
                    'auto_renewal' => $serviceUser->auto_renewal,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '구독 취소 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 취소된 구독 재활성화
     */
    public function reactivate(Request $request, $serviceUserId)
    {
        $request->validate([
            'reactivate_reason' => 'nullable|string|max:500',
            'extend_days' => 'nullable|integer|min:1|max:365',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $serviceUser = ServiceUser::findOrFail($serviceUserId);

        // 취소된 구독인지 확인
        if ($serviceUser->status !== 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => '취소된 구독만 재활성화할 수 있습니다.'
            ], 400);
        }

        try {
            \DB::transaction(function () use ($request, $serviceUser) {
                $originalExpiresAt = $serviceUser->expires_at;

                // 만료일 연장 (옵션)
                $newExpiresAt = $originalExpiresAt;
                if ($request->extend_days) {
                    $newExpiresAt = now()->addDays($request->extend_days);
                } elseif ($originalExpiresAt < now()) {
                    // 이미 만료된 경우 1개월 연장
                    $newExpiresAt = now()->addMonth();
                }

                // 구독 재활성화
                $serviceUser->update([
                    'status' => 'active',
                    'expires_at' => $newExpiresAt,
                    'cancelled_at' => null,
                    'cancel_reason' => null,
                    'auto_renewal' => true,
                    'next_billing_at' => $serviceUser->billing_cycle !== 'lifetime' ? $newExpiresAt : null,
                ]);

                // 재활성화 로그 기록
                ServiceSubscriptionLog::logAdminAction(
                    $serviceUser->id,
                    '구독 재활성화',
                    $request->reactivate_reason ?: '관리자가 구독을 재활성화했습니다.',
                    auth()->id(),
                    auth()->user()->name ?? 'Unknown Admin'
                );
            });

            return response()->json([
                'success' => true,
                'message' => '구독이 성공적으로 재활성화되었습니다.',
                'data' => [
                    'service_user_id' => $serviceUser->id,
                    'status' => $serviceUser->status,
                    'expires_at' => $serviceUser->expires_at,
                    'auto_renewal' => $serviceUser->auto_renewal,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '구독 재활성화 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }
}