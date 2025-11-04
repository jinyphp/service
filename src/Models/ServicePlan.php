<?php

namespace Jiny\Service\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServicePlan extends Model
{
    use SoftDeletes;

    protected $table = 'service_plans';

    protected $fillable = [
        'service_id',
        'plan_name',
        'plan_code',
        'description',
        'monthly_price',
        'quarterly_price',
        'yearly_price',
        'lifetime_price',
        'setup_fee',
        'discount_rules',
        'trial_period_days',
        'features',
        'limitations',
        'quotas',
        'plan_type',
        'billing_type',
        'monthly_available',
        'quarterly_available',
        'yearly_available',
        'lifetime_available',
        'is_active',
        'is_featured',
        'is_popular',
        'allow_trial',
        'auto_renewal',
        'sort_order',
        'color_code',
        'icon',
        'upgrade_paths',
        'downgrade_paths',
        'immediate_upgrade',
        'immediate_downgrade',
        'max_users',
        'max_projects',
        'storage_limit_gb',
        'api_calls_per_month',
        'available_regions',
        'restricted_regions',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'quarterly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'lifetime_price' => 'decimal:2',
        'setup_fee' => 'decimal:2',
        'trial_period_days' => 'decimal:0',
        'storage_limit_gb' => 'decimal:2',
        'discount_rules' => 'array',
        'features' => 'array',
        'limitations' => 'array',
        'quotas' => 'array',
        'upgrade_paths' => 'array',
        'downgrade_paths' => 'array',
        'available_regions' => 'array',
        'restricted_regions' => 'array',
        'monthly_available' => 'boolean',
        'quarterly_available' => 'boolean',
        'yearly_available' => 'boolean',
        'lifetime_available' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_popular' => 'boolean',
        'allow_trial' => 'boolean',
        'auto_renewal' => 'boolean',
        'immediate_upgrade' => 'boolean',
        'immediate_downgrade' => 'boolean',
    ];

    // Relationships
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function serviceUsers(): HasMany
    {
        return $this->hasMany(ServiceUser::class, 'plan_name', 'plan_name');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ServicePlanPrice::class, 'service_plan_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(ServicePlanDetail::class, 'service_plan_id');
    }

    public function activePrices(): HasMany
    {
        return $this->prices()->enabled()->ordered();
    }

    public function activeDetails(): HasMany
    {
        return $this->details()->enabled()->ordered();
    }

    public function features(): HasMany
    {
        return $this->details()->byType('feature');
    }

    public function limitations(): HasMany
    {
        return $this->details()->byType('limitation');
    }

    public function requirements(): HasMany
    {
        return $this->details()->byType('requirement');
    }

    public function benefits(): HasMany
    {
        return $this->details()->byType('benefit');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    public function scopeByService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('plan_type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('monthly_price');
    }

    public function scopeWithTrial($query)
    {
        return $query->where('allow_trial', true)
                    ->where('trial_period_days', '>', 0);
    }

    // Accessors & Mutators
    public function getPriceForCycleAttribute()
    {
        return function ($cycle) {
            switch ($cycle) {
                case 'monthly':
                    return $this->monthly_price;
                case 'quarterly':
                    return $this->quarterly_price;
                case 'yearly':
                    return $this->yearly_price;
                case 'lifetime':
                    return $this->lifetime_price;
                default:
                    return 0;
            }
        };
    }

    public function getAvailableCyclesAttribute(): array
    {
        $cycles = [];

        if ($this->monthly_available && $this->monthly_price > 0) {
            $cycles[] = 'monthly';
        }
        if ($this->quarterly_available && $this->quarterly_price > 0) {
            $cycles[] = 'quarterly';
        }
        if ($this->yearly_available && $this->yearly_price > 0) {
            $cycles[] = 'yearly';
        }
        if ($this->lifetime_available && $this->lifetime_price > 0) {
            $cycles[] = 'lifetime';
        }

        return $cycles;
    }

    public function getDiscountPercentageAttribute()
    {
        return function ($cycle) {
            if (!$this->monthly_price || $cycle === 'monthly') {
                return 0;
            }

            $monthlyEquivalent = match ($cycle) {
                'quarterly' => $this->monthly_price * 3,
                'yearly' => $this->monthly_price * 12,
                default => $this->monthly_price,
            };

            $actualPrice = match ($cycle) {
                'quarterly' => $this->quarterly_price,
                'yearly' => $this->yearly_price,
                default => $this->monthly_price,
            };

            if ($monthlyEquivalent <= 0 || $actualPrice <= 0) {
                return 0;
            }

            return round((($monthlyEquivalent - $actualPrice) / $monthlyEquivalent) * 100, 1);
        };
    }

    public function getHasTrialAttribute(): bool
    {
        return $this->allow_trial && $this->trial_period_days > 0;
    }

    // Helper Methods
    public function calculatePrice($billingCycle, $applyDiscount = true)
    {
        $basePrice = match ($billingCycle) {
            'monthly' => $this->monthly_price,
            'quarterly' => $this->quarterly_price,
            'yearly' => $this->yearly_price,
            'lifetime' => $this->lifetime_price,
            default => 0,
        };

        if (!$applyDiscount || !$this->discount_rules) {
            return $basePrice;
        }

        // 할인 규칙 적용 로직
        $discountAmount = 0;
        foreach ($this->discount_rules as $rule) {
            if ($this->isDiscountApplicable($rule, $billingCycle)) {
                $discountAmount += $this->calculateDiscount($rule, $basePrice);
            }
        }

        return max(0, $basePrice - $discountAmount);
    }

    public function isUpgradeAvailable($fromPlanCode): bool
    {
        return in_array($fromPlanCode, $this->upgrade_paths ?: []);
    }

    public function isDowngradeAvailable($fromPlanCode): bool
    {
        return in_array($fromPlanCode, $this->downgrade_paths ?: []);
    }

    public function getUpgradePrice($fromPlan, $billingCycle): float
    {
        if (!$this->isUpgradeAvailable($fromPlan->plan_code)) {
            return 0;
        }

        $newPrice = $this->calculatePrice($billingCycle);
        $currentPrice = $fromPlan->calculatePrice($billingCycle);

        return max(0, $newPrice - $currentPrice);
    }

    public function getDowngradeRefund($fromPlan, $billingCycle, $remainingDays): float
    {
        if (!$this->isDowngradeAvailable($fromPlan->plan_code)) {
            return 0;
        }

        $currentPrice = $fromPlan->calculatePrice($billingCycle);
        $newPrice = $this->calculatePrice($billingCycle);

        $priceDifference = $currentPrice - $newPrice;

        // 남은 기간에 대한 비례 환불 계산
        $totalDays = match ($billingCycle) {
            'monthly' => 30,
            'quarterly' => 90,
            'yearly' => 365,
            'lifetime' => 365 * 10, // 임시로 10년
            default => 30,
        };

        return max(0, ($priceDifference * $remainingDays) / $totalDays);
    }

    public function hasFeature($featureName): bool
    {
        return in_array($featureName, $this->features ?: []);
    }

    public function getQuota($quotaName)
    {
        return $this->quotas[$quotaName] ?? null;
    }

    public function getLimitation($limitationName)
    {
        return $this->limitations[$limitationName] ?? null;
    }

    public function isAvailableInRegion($region): bool
    {
        // 제한 지역이 설정되어 있고 해당 지역이 포함되면 사용 불가
        if ($this->restricted_regions && in_array($region, $this->restricted_regions)) {
            return false;
        }

        // 이용 가능 지역이 설정되어 있으면 해당 지역만 허용
        if ($this->available_regions) {
            return in_array($region, $this->available_regions);
        }

        // 특별한 제한이 없으면 모든 지역에서 이용 가능
        return true;
    }

    protected function isDiscountApplicable($rule, $billingCycle): bool
    {
        // 할인 규칙이 해당 결제 주기에 적용되는지 확인
        if (isset($rule['applicable_cycles'])) {
            return in_array($billingCycle, $rule['applicable_cycles']);
        }

        return true;
    }

    protected function calculateDiscount($rule, $basePrice): float
    {
        switch ($rule['type']) {
            case 'percentage':
                return $basePrice * ($rule['value'] / 100);
            case 'fixed':
                return $rule['value'];
            default:
                return 0;
        }
    }
}