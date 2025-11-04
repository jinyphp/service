<?php

namespace Jiny\Service\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class SiteService extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'services';

    protected $fillable = [
        'enable',
        'featured',
        'slug',
        'title',
        'description',
        'content',
        'category',
        'category_id',
        'price',
        'duration',
        'image',
        'images',
        'features',
        'process',
        'requirements',
        'deliverables',
        'tags',
        'meta_title',
        'meta_description',
        'manager',
    ];

    protected $casts = [
        'enable' => 'boolean',
        'featured' => 'boolean',
        'price' => 'decimal:2',
        'images' => 'array',
        'features' => 'array',
        'process' => 'array',
        'requirements' => 'array',
        'deliverables' => 'array',
    ];

    protected $dates = [
        'deleted_at',
    ];

    /**
     * 활성화된 서비스만 조회
     */
    public function scopeEnabled($query)
    {
        return $query->where('enable', true);
    }

    /**
     * 추천 서비스만 조회
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * 카테고리별 조회
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * 검색
     */
    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('title', 'like', '%' . $keyword . '%')
              ->orWhere('description', 'like', '%' . $keyword . '%')
              ->orWhere('content', 'like', '%' . $keyword . '%')
              ->orWhere('tags', 'like', '%' . $keyword . '%');
        });
    }

    /**
     * 가격 범위로 조회
     */
    public function scopePriceRange($query, $min = null, $max = null)
    {
        if ($min !== null) {
            $query->where('price', '>=', $min);
        }
        if ($max !== null) {
            $query->where('price', '<=', $max);
        }
        return $query;
    }

    /**
     * 기간별 조회
     */
    public function scopeByDuration($query, $duration)
    {
        return $query->where('duration', 'like', '%' . $duration . '%');
    }

    /**
     * 설명 요약
     */
    public function getExcerptAttribute($length = 150)
    {
        return \Str::limit(strip_tags($this->description), $length);
    }

    /**
     * 메인 이미지 URL
     */
    public function getMainImageAttribute()
    {
        return $this->image ?: (is_array($this->images) && count($this->images) > 0 ? $this->images[0] : null);
    }

    /**
     * 태그 배열
     */
    public function getTagListAttribute()
    {
        return $this->tags ? explode(',', $this->tags) : [];
    }

    /**
     * 서비스 프로세스 단계 수
     */
    public function getProcessStepsCountAttribute()
    {
        return is_array($this->process) ? count($this->process) : 0;
    }

    /**
     * 요구사항 수
     */
    public function getRequirementsCountAttribute()
    {
        return is_array($this->requirements) ? count($this->requirements) : 0;
    }

    /**
     * 결과물 수
     */
    public function getDeliverablesCountAttribute()
    {
        return is_array($this->deliverables) ? count($this->deliverables) : 0;
    }

    /**
     * 서비스 특징 수
     */
    public function getFeaturesCountAttribute()
    {
        return is_array($this->features) ? count($this->features) : 0;
    }

    /**
     * 카테고리와의 관계
     */
    public function serviceCategory()
    {
        return $this->belongsTo(\Jiny\Service\Models\ServiceCategory::class, 'category_id');
    }

    /**
     * 서비스 플랜들과의 관계
     */
    public function pricingOptions()
    {
        return $this->hasMany(\Jiny\Service\Models\ServicePlan::class, 'service_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    /**
     * 기본 서비스 플랜
     */
    public function defaultPricing()
    {
        return $this->hasOne(\Jiny\Service\Models\ServicePlan::class, 'service_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    /**
     * 카테고리별 조회 (새로운 방식)
     */
    public function scopeByCategoryId($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * 가격 범위 조회 (서비스 플랜 기반)
     */
    public function scopePriceRangeFromOptions($query, $min = null, $max = null)
    {
        return $query->whereHas('pricingOptions', function ($q) use ($min, $max) {
            if ($min !== null) {
                $q->where(function ($sq) use ($min) {
                    $sq->where('monthly_price', '>=', $min)
                      ->orWhere('quarterly_price', '>=', $min)
                      ->orWhere('yearly_price', '>=', $min);
                });
            }
            if ($max !== null) {
                $q->where(function ($sq) use ($max) {
                    $sq->where('monthly_price', '<=', $max)
                      ->orWhere('quarterly_price', '<=', $max)
                      ->orWhere('yearly_price', '<=', $max);
                });
            }
        });
    }

    /**
     * 최저가격 (서비스 플랜 기반)
     */
    public function getMinPriceAttribute()
    {
        $plans = $this->pricingOptions()->get();

        if ($plans->isEmpty()) {
            return $this->price ?? 0;
        }

        $minPrice = null;
        foreach ($plans as $plan) {
            $prices = array_filter([
                $plan->monthly_price,
                $plan->quarterly_price,
                $plan->yearly_price
            ], function($price) {
                return $price > 0;
            });

            if (!empty($prices)) {
                $planMinPrice = min($prices);
                if ($minPrice === null || $planMinPrice < $minPrice) {
                    $minPrice = $planMinPrice;
                }
            }
        }

        return $minPrice ?? 0;
    }

    /**
     * 가격 옵션이 있는지 확인
     */
    public function getHasPricingOptionsAttribute()
    {
        return $this->pricingOptions()->count() > 0;
    }

    /**
     * 서비스 가격들과의 관계
     */
    public function prices()
    {
        return $this->hasMany(\Jiny\Service\Models\ServicePrice::class, 'service_id')
            ->where('enable', true)
            ->orderBy('pos');
    }

    /**
     * 활성화된 가격들
     */
    public function activePrices()
    {
        return $this->hasMany(\Jiny\Service\Models\ServicePrice::class, 'service_id')
            ->active()
            ->valid()
            ->ordered();
    }

    /**
     * 첫 번째 가격 (기본 가격 대용)
     */
    public function defaultPrice()
    {
        return $this->hasOne(\Jiny\Service\Models\ServicePrice::class, 'service_id')
            ->where('enable', true)
            ->orderBy('pos');
    }

    /**
     * 인기 가격
     */
    public function popularPrice()
    {
        return $this->hasOne(\Jiny\Service\Models\ServicePrice::class, 'service_id')
            ->where('is_popular', true)
            ->where('enable', true);
    }

    /**
     * 추천 가격
     */
    public function recommendedPrice()
    {
        return $this->hasOne(\Jiny\Service\Models\ServicePrice::class, 'service_id')
            ->where('is_recommended', true)
            ->where('enable', true);
    }

    /**
     * 최저 가격 (서비스 가격 기반)
     */
    public function getMinServicePriceAttribute()
    {
        $prices = $this->activePrices()->get();

        if ($prices->isEmpty()) {
            return $this->price ?? 0;
        }

        return $prices->min('effective_price') ?? 0;
    }

    /**
     * 서비스 가격이 있는지 확인
     */
    public function getHasServicePricesAttribute()
    {
        return $this->prices()->count() > 0;
    }
}