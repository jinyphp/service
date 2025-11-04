<?php

namespace Jiny\Service\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicePlanPrice extends Model
{
    use SoftDeletes;

    protected $table = 'service_plan_price';

    protected $fillable = [
        'service_plan_id',
        'enable',
        'pos',
        'name',
        'code',
        'description',
        'price',
        'sale_price',
        'currency',
        'billing_period',
        'billing_cycle_count',
        'discount_percentage',
        'setup_fee',
        'trial_days',
        'additional_features',
        'pricing_rules',
        'auto_renewal',
        'is_popular',
        'is_recommended',
        'min_quantity',
        'max_quantity',
        'valid_from',
        'valid_until',
    ];

    protected $casts = [
        'enable' => 'boolean',
        'pos' => 'integer',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'billing_cycle_count' => 'integer',
        'discount_percentage' => 'decimal:2',
        'setup_fee' => 'decimal:2',
        'trial_days' => 'integer',
        'additional_features' => 'array',
        'pricing_rules' => 'array',
        'auto_renewal' => 'boolean',
        'is_popular' => 'boolean',
        'is_recommended' => 'boolean',
        'min_quantity' => 'integer',
        'max_quantity' => 'integer',
        'valid_from' => 'date',
        'valid_until' => 'date',
    ];

    // Relationships
    public function servicePlan(): BelongsTo
    {
        return $this->belongsTo(ServicePlan::class, 'service_plan_id');
    }

    // Scopes
    public function scopeEnabled($query)
    {
        return $query->where('enable', true);
    }

    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    public function scopeRecommended($query)
    {
        return $query->where('is_recommended', true);
    }

    public function scopeByPeriod($query, $period)
    {
        return $query->where('billing_period', $period);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('pos')->orderBy('price');
    }

    public function scopeValid($query)
    {
        $now = now();
        return $query->where(function ($q) use ($now) {
            $q->whereNull('valid_from')
              ->orWhere('valid_from', '<=', $now);
        })->where(function ($q) use ($now) {
            $q->whereNull('valid_until')
              ->orWhere('valid_until', '>=', $now);
        });
    }

    // Accessors & Mutators
    public function getEffectivePriceAttribute()
    {
        return $this->sale_price ?? $this->price;
    }

    public function getHasDiscountAttribute(): bool
    {
        return $this->sale_price !== null && $this->sale_price < $this->price;
    }

    public function getDiscountAmountAttribute()
    {
        if (!$this->has_discount) {
            return 0;
        }
        return $this->price - $this->sale_price;
    }

    public function getActualDiscountPercentageAttribute()
    {
        if (!$this->has_discount || $this->price <= 0) {
            return 0;
        }
        return round((($this->price - $this->sale_price) / $this->price) * 100, 1);
    }

    public function getHasTrialAttribute(): bool
    {
        return $this->trial_days > 0;
    }

    public function getPeriodDisplayAttribute(): string
    {
        return match ($this->billing_period) {
            'monthly' => '월간',
            'quarterly' => '분기',
            'yearly' => '연간',
            'once' => '일회성',
            default => $this->billing_period,
        };
    }

    public function getCurrencySymbolAttribute(): string
    {
        return match ($this->currency) {
            'KRW' => '₩',
            'USD' => '$',
            'JPY' => '¥',
            'EUR' => '€',
            default => $this->currency,
        };
    }

    public function getFormattedPriceAttribute(): string
    {
        return $this->currency_symbol . number_format($this->price);
    }

    public function getFormattedEffectivePriceAttribute(): string
    {
        return $this->currency_symbol . number_format($this->effective_price);
    }

    // Helper Methods
    public function isValidNow(): bool
    {
        $now = now();

        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }

        return true;
    }

    public function calculateTotalPrice($quantity = 1): float
    {
        $basePrice = $this->effective_price * $quantity;
        $setupFee = $this->setup_fee;

        return $basePrice + $setupFee;
    }

    public function getNextBillingDate($startDate = null): ?\Carbon\Carbon
    {
        if ($this->billing_period === 'once') {
            return null;
        }

        $start = $startDate ? \Carbon\Carbon::parse($startDate) : now();

        return match ($this->billing_period) {
            'monthly' => $start->addMonths($this->billing_cycle_count),
            'quarterly' => $start->addMonths(3 * $this->billing_cycle_count),
            'yearly' => $start->addYears($this->billing_cycle_count),
            default => null,
        };
    }

    public function hasFeature($featureName): bool
    {
        if (!$this->additional_features) {
            return false;
        }

        return in_array($featureName, $this->additional_features);
    }

    public function applyPricingRules($context = []): float
    {
        $price = $this->effective_price;

        if (!$this->pricing_rules) {
            return $price;
        }

        foreach ($this->pricing_rules as $rule) {
            $price = $this->applyRule($price, $rule, $context);
        }

        return max(0, $price);
    }

    protected function applyRule($price, $rule, $context): float
    {
        if (!isset($rule['type'])) {
            return $price;
        }

        // 조건 확인
        if (isset($rule['conditions']) && !$this->checkConditions($rule['conditions'], $context)) {
            return $price;
        }

        return match ($rule['type']) {
            'percentage_discount' => $price * (1 - ($rule['value'] / 100)),
            'fixed_discount' => $price - $rule['value'],
            'percentage_markup' => $price * (1 + ($rule['value'] / 100)),
            'fixed_markup' => $price + $rule['value'],
            default => $price,
        };
    }

    protected function checkConditions($conditions, $context): bool
    {
        foreach ($conditions as $condition) {
            if (!$this->checkCondition($condition, $context)) {
                return false;
            }
        }
        return true;
    }

    protected function checkCondition($condition, $context): bool
    {
        $field = $condition['field'] ?? '';
        $operator = $condition['operator'] ?? '=';
        $value = $condition['value'] ?? null;
        $contextValue = $context[$field] ?? null;

        return match ($operator) {
            '=' => $contextValue == $value,
            '!=' => $contextValue != $value,
            '>' => $contextValue > $value,
            '>=' => $contextValue >= $value,
            '<' => $contextValue < $value,
            '<=' => $contextValue <= $value,
            'in' => is_array($value) && in_array($contextValue, $value),
            'not_in' => is_array($value) && !in_array($contextValue, $value),
            default => false,
        };
    }
}