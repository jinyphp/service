<?php

namespace Jiny\Service\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\ServiceUserFactory;

class ServiceUser extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return ServiceUserFactory::new();
    }

    protected $table = 'service_users';

    protected $fillable = [
        'user_uuid',
        'user_shard',
        'user_id',
        'user_email',
        'user_name',
        'service_id',
        'service_title',
        'status',
        'billing_cycle',
        'started_at',
        'expires_at',
        'next_billing_at',
        'plan_name',
        'plan_price',
        'plan_features',
        'monthly_price',
        'total_paid',
        'payment_method',
        'payment_status',
        'auto_renewal',
        'auto_upgrade',
        'cancelled_at',
        'cancel_reason',
        'refund_amount',
        'refunded_at',
        'admin_notes',
    ];

    protected $casts = [
        'plan_features' => 'array',
        'plan_price' => 'decimal:2',
        'monthly_price' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'auto_renewal' => 'boolean',
        'auto_upgrade' => 'boolean',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'next_billing_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    protected $dates = [
        'started_at',
        'expires_at',
        'next_billing_at',
        'cancelled_at',
        'refunded_at',
        'deleted_at',
    ];

    // Relationships
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ServicePayment::class, 'service_user_id');
    }

    public function subscriptionLogs(): HasMany
    {
        return $this->hasMany(ServiceSubscriptionLog::class, 'service_user_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
                    ->orWhere('expires_at', '<', now());
    }

    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->where('status', 'active')
                    ->where('expires_at', '<=', now()->addDays($days));
    }

    public function scopeByUser($query, $userUuid)
    {
        return $query->where('user_uuid', $userUuid);
    }

    public function scopeByService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    // Accessors & Mutators
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active' && $this->expires_at > now();
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at < now() || $this->status === 'expired';
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->expires_at <= now()->addDays(7) && $this->status === 'active';
    }

    public function getDaysUntilExpiryAttribute(): int
    {
        return $this->expires_at ? now()->diffInDays($this->expires_at, false) : 0;
    }

    // Helper Methods
    public function getUserFromShard()
    {
        if (!$this->user_shard || !$this->user_id) {
            return null;
        }

        try {
            // 샤딩된 사용자 테이블에서 사용자 정보 조회
            return \DB::table($this->user_shard)
                     ->where('id', $this->user_id)
                     ->first();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function updateUserCache()
    {
        $user = $this->getUserFromShard();
        if ($user) {
            $this->update([
                'user_email' => $user->email,
                'user_name' => $user->name ?? $user->username,
            ]);
        }
    }

    public function calculateNextBilling()
    {
        if (!$this->next_billing_at) {
            return null;
        }

        switch ($this->billing_cycle) {
            case 'monthly':
                return $this->next_billing_at->addMonth();
            case 'quarterly':
                return $this->next_billing_at->addMonths(3);
            case 'yearly':
                return $this->next_billing_at->addYear();
            default:
                return null;
        }
    }

    public function extend($days)
    {
        if ($this->expires_at) {
            $this->expires_at = $this->expires_at->addDays($days);
        } else {
            $this->expires_at = now()->addDays($days);
        }

        if ($this->next_billing_at) {
            $this->next_billing_at = $this->next_billing_at->addDays($days);
        }

        $this->save();

        // 로그 기록
        $this->subscriptionLogs()->create([
            'user_uuid' => $this->user_uuid,
            'service_id' => $this->service_id,
            'action' => 'extend',
            'action_title' => '구독 연장',
            'action_description' => "{$days}일 연장",
            'status_before' => $this->status,
            'status_after' => $this->status,
            'expires_before' => $this->expires_at->subDays($days),
            'expires_after' => $this->expires_at,
            'processed_by' => 'system',
        ]);
    }

    public function cancel($reason = null)
    {
        $previousStatus = $this->status;

        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancel_reason' => $reason,
            'auto_renewal' => false,
        ]);

        // 로그 기록
        $this->subscriptionLogs()->create([
            'user_uuid' => $this->user_uuid,
            'service_id' => $this->service_id,
            'action' => 'cancel',
            'action_title' => '구독 취소',
            'action_description' => $reason ?: '구독이 취소되었습니다.',
            'status_before' => $previousStatus,
            'status_after' => 'cancelled',
            'processed_by' => 'system',
        ]);
    }

    public function activate()
    {
        $previousStatus = $this->status;

        $this->update([
            'status' => 'active',
            'started_at' => $this->started_at ?: now(),
        ]);

        // 로그 기록
        $this->subscriptionLogs()->create([
            'user_uuid' => $this->user_uuid,
            'service_id' => $this->service_id,
            'action' => 'activate',
            'action_title' => '구독 활성화',
            'action_description' => '구독이 활성화되었습니다.',
            'status_before' => $previousStatus,
            'status_after' => 'active',
            'processed_by' => 'system',
        ]);
    }
}