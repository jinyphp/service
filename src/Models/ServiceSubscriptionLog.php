<?php

namespace Jiny\Service\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceSubscriptionLog extends Model
{
    protected $table = 'service_subscription_logs';

    protected $fillable = [
        'service_user_id',
        'user_uuid',
        'service_id',
        'action',
        'action_title',
        'action_description',
        'action_data',
        'status_before',
        'status_after',
        'amount',
        'currency',
        'plan_before',
        'plan_after',
        'expires_before',
        'expires_after',
        'processed_by',
        'processor_id',
        'processor_name',
        'result',
        'error_message',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'action_data' => 'array',
        'amount' => 'decimal:2',
        'expires_before' => 'datetime',
        'expires_after' => 'datetime',
    ];

    // Relationships
    public function serviceUser(): BelongsTo
    {
        return $this->belongsTo(ServiceUser::class, 'service_user_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    // Scopes
    public function scopeByUser($query, $userUuid)
    {
        return $query->where('user_uuid', $userUuid);
    }

    public function scopeByService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('result', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('result', 'failed');
    }

    public function scopeByProcessor($query, $processorType)
    {
        return $query->where('processed_by', $processorType);
    }

    public function scopeRecentFirst($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Helper Methods
    public static function logAction($serviceUserId, $action, $data = [])
    {
        $serviceUser = ServiceUser::find($serviceUserId);
        if (!$serviceUser) {
            return null;
        }

        $logData = array_merge([
            'service_user_id' => $serviceUserId,
            'user_uuid' => $serviceUser->user_uuid,
            'service_id' => $serviceUser->service_id,
            'action' => $action,
            'processed_by' => 'system',
            'result' => 'success',
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ], $data);

        return static::create($logData);
    }

    public static function logPaymentSuccess($serviceUserId, $amount, $paymentMethod)
    {
        return static::logAction($serviceUserId, 'payment_success', [
            'action_title' => '결제 성공',
            'action_description' => "결제가 성공적으로 완료되었습니다. ({$paymentMethod})",
            'amount' => $amount,
            'action_data' => [
                'payment_method' => $paymentMethod,
                'payment_amount' => $amount,
            ],
        ]);
    }

    public static function logPaymentFailed($serviceUserId, $amount, $failureReason)
    {
        return static::logAction($serviceUserId, 'payment_failed', [
            'action_title' => '결제 실패',
            'action_description' => "결제가 실패했습니다: {$failureReason}",
            'amount' => $amount,
            'result' => 'failed',
            'error_message' => $failureReason,
            'action_data' => [
                'payment_amount' => $amount,
                'failure_reason' => $failureReason,
            ],
        ]);
    }

    public static function logSubscribe($serviceUserId, $planName, $expiresAt)
    {
        return static::logAction($serviceUserId, 'subscribe', [
            'action_title' => '구독 신청',
            'action_description' => "'{$planName}' 플랜에 구독하였습니다.",
            'status_after' => 'active',
            'plan_after' => $planName,
            'expires_after' => $expiresAt,
            'action_data' => [
                'plan_name' => $planName,
                'expires_at' => $expiresAt,
            ],
        ]);
    }

    public static function logCancel($serviceUserId, $reason = null)
    {
        $serviceUser = ServiceUser::find($serviceUserId);

        return static::logAction($serviceUserId, 'cancel', [
            'action_title' => '구독 취소',
            'action_description' => $reason ?: '구독이 취소되었습니다.',
            'status_before' => $serviceUser->status ?? 'unknown',
            'status_after' => 'cancelled',
            'action_data' => [
                'cancel_reason' => $reason,
                'cancelled_at' => now(),
            ],
        ]);
    }

    public static function logUpgrade($serviceUserId, $fromPlan, $toPlan, $amount = null)
    {
        return static::logAction($serviceUserId, 'upgrade', [
            'action_title' => '플랜 업그레이드',
            'action_description' => "'{$fromPlan}'에서 '{$toPlan}'으로 업그레이드하였습니다.",
            'plan_before' => $fromPlan,
            'plan_after' => $toPlan,
            'amount' => $amount,
            'action_data' => [
                'from_plan' => $fromPlan,
                'to_plan' => $toPlan,
                'upgrade_amount' => $amount,
            ],
        ]);
    }

    public static function logDowngrade($serviceUserId, $fromPlan, $toPlan, $refundAmount = null)
    {
        return static::logAction($serviceUserId, 'downgrade', [
            'action_title' => '플랜 다운그레이드',
            'action_description' => "'{$fromPlan}'에서 '{$toPlan}'으로 다운그레이드하였습니다.",
            'plan_before' => $fromPlan,
            'plan_after' => $toPlan,
            'amount' => $refundAmount ? -$refundAmount : null,
            'action_data' => [
                'from_plan' => $fromPlan,
                'to_plan' => $toPlan,
                'refund_amount' => $refundAmount,
            ],
        ]);
    }

    public static function logRenew($serviceUserId, $amount, $newExpiresAt)
    {
        $serviceUser = ServiceUser::find($serviceUserId);

        return static::logAction($serviceUserId, 'renew', [
            'action_title' => '구독 갱신',
            'action_description' => '구독이 자동으로 갱신되었습니다.',
            'amount' => $amount,
            'expires_before' => $serviceUser->expires_at,
            'expires_after' => $newExpiresAt,
            'action_data' => [
                'renewal_amount' => $amount,
                'new_expires_at' => $newExpiresAt,
            ],
        ]);
    }

    public static function logRefund($serviceUserId, $refundAmount, $reason = null)
    {
        return static::logAction($serviceUserId, 'refund', [
            'action_title' => '환불 처리',
            'action_description' => $reason ?: '환불이 처리되었습니다.',
            'amount' => -$refundAmount,
            'action_data' => [
                'refund_amount' => $refundAmount,
                'refund_reason' => $reason,
                'refunded_at' => now(),
            ],
        ]);
    }

    public static function logAdminAction($serviceUserId, $actionTitle, $description, $adminId = null, $adminName = null)
    {
        return static::logAction($serviceUserId, 'admin_action', [
            'action_title' => $actionTitle,
            'action_description' => $description,
            'processed_by' => 'admin',
            'processor_id' => $adminId,
            'processor_name' => $adminName,
            'action_data' => [
                'admin_action' => true,
                'admin_id' => $adminId,
                'admin_name' => $adminName,
            ],
        ]);
    }
}