<?php

namespace Jiny\Service\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicePrice extends Model
{
    use SoftDeletes;

    protected $table = 'service_plan_price';

    protected $fillable = [
        'service_id',
        'service_plan_id',
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
        'min_quantity',
        'max_quantity',
        'additional_features',
        'pricing_rules',
        'valid_from',
        'valid_until',
        'auto_renewal',
        'is_popular',
        'is_recommended',
        'pos',
        'enable',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'setup_fee' => 'decimal:2',
        'trial_days' => 'integer',
        'billing_cycle_count' => 'integer',
        'min_quantity' => 'integer',
        'max_quantity' => 'integer',
        'additional_features' => 'array',
        'pricing_rules' => 'array',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'auto_renewal' => 'boolean',
        'is_popular' => 'boolean',
        'is_recommended' => 'boolean',
        'pos' => 'integer',
        'enable' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    /**
     * 서비스와의 관계
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(SiteService::class, 'service_id');
    }

    /**
     * 활성 가격 옵션만 조회
     */
    public function scopeActive($query)
    {
        return $query->where('enable', true);
    }

    /**
     * 유효한 가격 옵션만 조회 (유효 기간 내)
     */
    public function scopeValid($query)
    {
        $now = now()->toDateString();
        return $query->where(function ($q) use ($now) {
            $q->where(function ($subQ) use ($now) {
                $subQ->whereNull('valid_from')
                     ->orWhere('valid_from', '<=', $now);
            })->where(function ($subQ) use ($now) {
                $subQ->whereNull('valid_until')
                     ->orWhere('valid_until', '>=', $now);
            });
        });
    }

    /**
     * 인기 옵션만 조회
     */
    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    /**
     * 추천 옵션만 조회
     */
    public function scopeRecommended($query)
    {
        return $query->where('is_recommended', true);
    }

    /**
     * 정렬 순서로 조회
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('pos', 'asc')
                    ->orderBy('price', 'asc');
    }

    /**
     * 무료체험 제공 여부
     */
    public function getHasTrialAttribute(): bool
    {
        return $this->trial_days > 0;
    }

    /**
     * 할인이 적용된 가격인지 확인
     */
    public function getHasDiscountAttribute(): bool
    {
        if (!$this->sale_price) {
            return false;
        }

        return $this->sale_price < $this->price;
    }

    /**
     * 실제 적용되는 가격 (할인가 우선)
     */
    public function getEffectivePriceAttribute()
    {
        return $this->has_discount ? $this->sale_price : $this->price;
    }

    /**
     * 무료 가격인지 확인
     */
    public function getIsFreeAttribute(): bool
    {
        return $this->price === null || $this->price == 0;
    }

    /**
     * 포맷된 가격 표시
     */
    public function getFormattedPriceAttribute(): string
    {
        if ($this->is_free) {
            return '무료';
        }

        return $this->currency === 'KRW'
            ? '₩' . number_format($this->price)
            : $this->currency . ' ' . number_format($this->price, 2);
    }

    /**
     * 포맷된 할인가 표시
     */
    public function getFormattedSalePriceAttribute(): string
    {
        if (!$this->sale_price) {
            return '';
        }

        if ($this->sale_price == 0) {
            return '무료';
        }

        return $this->currency === 'KRW'
            ? '₩' . number_format($this->sale_price)
            : $this->currency . ' ' . number_format($this->sale_price, 2);
    }

    /**
     * 포맷된 유효 가격 표시
     */
    public function getFormattedEffectivePriceAttribute(): string
    {
        $price = $this->effective_price;

        if ($price === null || $price == 0) {
            return '무료';
        }

        return $this->currency === 'KRW'
            ? '₩' . number_format($price)
            : $this->currency . ' ' . number_format($price, 2);
    }

    /**
     * 실제 할인율 계산
     */
    public function getActualDiscountPercentageAttribute(): float
    {
        if (!$this->has_discount || !$this->price) {
            return 0;
        }

        return round((($this->price - $this->sale_price) / $this->price) * 100, 1);
    }

    /**
     * 설정비 포함 총 가격
     */
    public function getTotalPriceAttribute()
    {
        return $this->effective_price + ($this->setup_fee ?? 0);
    }

    /**
     * 포맷된 설정비 표시
     */
    public function getFormattedSetupFeeAttribute(): string
    {
        if (!$this->setup_fee) {
            return '';
        }

        return $this->currency === 'KRW'
            ? '₩' . number_format($this->setup_fee)
            : $this->currency . ' ' . number_format($this->setup_fee, 2);
    }

    /**
     * 결제 주기 표시
     */
    public function getBillingPeriodDisplayAttribute(): string
    {
        $periods = [
            'monthly' => '월간',
            'quarterly' => '분기',
            'yearly' => '연간',
            'once' => '일회성',
        ];

        return $periods[$this->billing_period] ?? $this->billing_period;
    }

    /**
     * 통화 표시
     */
    public function getCurrencyDisplayAttribute(): string
    {
        $currencies = [
            'KRW' => '원',
            'USD' => '달러',
            'EUR' => '유로',
            'JPY' => '엔',
        ];

        return $currencies[$this->currency] ?? $this->currency;
    }

    /**
     * 현재 유효한 가격인지 확인
     */
    public function getIsCurrentlyValidAttribute(): bool
    {
        if (!$this->enable) {
            return false;
        }

        $now = now()->toDateString();

        if ($this->valid_from && $now < $this->valid_from) {
            return false;
        }

        if ($this->valid_until && $now > $this->valid_until) {
            return false;
        }

        return true;
    }


    /**
     * 인기 옵션으로 설정
     */
    public function setAsPopular(): bool
    {
        // 같은 서비스의 다른 옵션들을 인기에서 해제
        static::where('service_id', $this->service_id)
              ->where('id', '!=', $this->id)
              ->update(['is_popular' => false]);

        // 현재 옵션을 인기로 설정
        return $this->update(['is_popular' => true]);
    }

    /**
     * 추천 옵션으로 설정
     */
    public function setAsRecommended(): bool
    {
        // 같은 서비스의 다른 옵션들을 추천에서 해제
        static::where('service_id', $this->service_id)
              ->where('id', '!=', $this->id)
              ->update(['is_recommended' => false]);

        // 현재 옵션을 추천으로 설정
        return $this->update(['is_recommended' => true]);
    }
}