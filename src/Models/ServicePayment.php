<?php

namespace Jiny\Service\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ServicePayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'service_payments';

    protected $fillable = [
        'service_user_id',
        'user_uuid',
        'service_id',
        'payment_uuid',
        'transaction_id',
        'order_id',
        'amount',
        'tax_amount',
        'discount_amount',
        'final_amount',
        'currency',
        'payment_method',
        'payment_provider',
        'payment_details',
        'status',
        'payment_type',
        'billing_cycle',
        'billing_period_start',
        'billing_period_end',
        'refunded_amount',
        'refunded_at',
        'refund_reason',
        'refund_transaction_id',
        'failure_code',
        'failure_message',
        'retry_count',
        'paid_at',
        'due_date',
        'metadata',
        'admin_notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'payment_details' => 'array',
        'metadata' => 'array',
        'billing_period_start' => 'datetime',
        'billing_period_end' => 'datetime',
        'refunded_at' => 'datetime',
        'paid_at' => 'datetime',
        'due_date' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->payment_uuid)) {
                $payment->payment_uuid = Str::uuid();
            }
        });
    }

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
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeRefunded($query)
    {
        return $query->whereIn('status', ['refunded', 'partially_refunded']);
    }

    public function scopeByUser($query, $userUuid)
    {
        return $query->where('user_uuid', $userUuid);
    }

    public function scopeByService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
                    ->where('due_date', '<', now());
    }

    // Accessors & Mutators
    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending';
    }

    public function getIsFailedAttribute(): bool
    {
        return $this->status === 'failed';
    }

    public function getIsRefundedAttribute(): bool
    {
        return in_array($this->status, ['refunded', 'partially_refunded']);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pending' && $this->due_date && $this->due_date < now();
    }

    public function getRefundableAmountAttribute(): float
    {
        if (!$this->is_completed) {
            return 0;
        }

        return $this->final_amount - $this->refunded_amount;
    }

    // Helper Methods
    public function markAsCompleted($transactionId = null, $paidAt = null)
    {
        $this->update([
            'status' => 'completed',
            'transaction_id' => $transactionId ?: $this->transaction_id,
            'paid_at' => $paidAt ?: now(),
            'failure_code' => null,
            'failure_message' => null,
        ]);

        // 구독 사용자 정보 업데이트
        if ($this->serviceUser) {
            $this->serviceUser->increment('total_paid', $this->final_amount);
            $this->serviceUser->update(['payment_status' => 'completed']);
        }

        return $this;
    }

    public function markAsFailed($failureCode = null, $failureMessage = null)
    {
        $this->update([
            'status' => 'failed',
            'failure_code' => $failureCode,
            'failure_message' => $failureMessage,
        ]);

        // 구독 사용자 정보 업데이트
        if ($this->serviceUser) {
            $this->serviceUser->update(['payment_status' => 'failed']);
        }

        return $this;
    }

    public function processRefund($amount = null, $reason = null, $refundTransactionId = null)
    {
        $refundAmount = $amount ?: $this->refundable_amount;

        if ($refundAmount <= 0) {
            throw new \Exception('환불 가능한 금액이 없습니다.');
        }

        if ($refundAmount > $this->refundable_amount) {
            throw new \Exception('환불 금액이 환불 가능한 금액을 초과합니다.');
        }

        $newRefundedAmount = $this->refunded_amount + $refundAmount;
        $isPartialRefund = $newRefundedAmount < $this->final_amount;

        $this->update([
            'status' => $isPartialRefund ? 'partially_refunded' : 'refunded',
            'refunded_amount' => $newRefundedAmount,
            'refunded_at' => now(),
            'refund_reason' => $reason,
            'refund_transaction_id' => $refundTransactionId,
        ]);

        // 구독 사용자 정보 업데이트
        if ($this->serviceUser) {
            $this->serviceUser->increment('refund_amount', $refundAmount);
            $this->serviceUser->update(['refunded_at' => now()]);
        }

        return $this;
    }

    public function retry()
    {
        if ($this->status !== 'failed') {
            throw new \Exception('실패한 결제만 재시도할 수 있습니다.');
        }

        $this->update([
            'status' => 'pending',
            'retry_count' => $this->retry_count + 1,
            'failure_code' => null,
            'failure_message' => null,
        ]);

        return $this;
    }

    public function cancel()
    {
        if (!in_array($this->status, ['pending', 'processing'])) {
            throw new \Exception('대기 중이거나 처리 중인 결제만 취소할 수 있습니다.');
        }

        $this->update([
            'status' => 'cancelled',
        ]);

        return $this;
    }
}