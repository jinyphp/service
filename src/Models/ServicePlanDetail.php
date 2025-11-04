<?php

namespace Jiny\Service\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicePlanDetail extends Model
{
    use SoftDeletes;

    protected $table = 'service_plan_detail';

    protected $fillable = [
        'service_id',
        'service_plan_id',
        'enable',
        'pos',
        'detail_type',
        'title',
        'description',
        'icon',
        'color',
        'value',
        'value_type',
        'unit',
        'is_highlighted',
        'show_in_comparison',
        'show_in_summary',
        'conditions',
        'tooltip',
        'link_url',
        'link_text',
        'category',
        'group_name',
        'group_order',
    ];

    protected $casts = [
        'enable' => 'boolean',
        'pos' => 'integer',
        'is_highlighted' => 'boolean',
        'show_in_comparison' => 'boolean',
        'show_in_summary' => 'boolean',
        'conditions' => 'array',
        'group_order' => 'integer',
    ];

    // Relationships
    public function service(): BelongsTo
    {
        return $this->belongsTo(SiteService::class, 'service_id');
    }

    public function servicePlan(): BelongsTo
    {
        return $this->belongsTo(ServicePlan::class, 'service_plan_id');
    }

    // Scopes
    public function scopeEnabled($query)
    {
        return $query->where('enable', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('detail_type', $type);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByGroup($query, $group)
    {
        return $query->where('group_name', $group);
    }

    public function scopeHighlighted($query)
    {
        return $query->where('is_highlighted', true);
    }

    public function scopeForComparison($query)
    {
        return $query->where('show_in_comparison', true);
    }

    public function scopeForSummary($query)
    {
        return $query->where('show_in_summary', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('group_order')
                    ->orderBy('pos')
                    ->orderBy('title');
    }

    public function scopeGrouped($query)
    {
        return $query->orderBy('category')
                    ->orderBy('group_name')
                    ->orderBy('group_order')
                    ->orderBy('pos');
    }

    // Accessors & Mutators
    public function getFormattedValueAttribute(): string
    {
        $value = $this->value;

        if ($value === null || $value === '') {
            return '-';
        }

        return match ($this->value_type) {
            'boolean' => $this->getBooleanDisplay($value),
            'number' => $this->getNumberDisplay($value),
            'json' => $this->getJsonDisplay($value),
            'html' => $value,
            default => $value,
        };
    }

    public function getDisplayValueAttribute(): string
    {
        $formatted = $this->formatted_value;

        if ($this->unit && $formatted !== '-') {
            $formatted .= ' ' . $this->unit;
        }

        return $formatted;
    }

    public function getTypeDisplayAttribute(): string
    {
        return match ($this->detail_type) {
            'feature' => '기능',
            'limitation' => '제한사항',
            'requirement' => '요구사항',
            'benefit' => '혜택',
            'support' => '지원',
            'addon' => '부가기능',
            default => $this->detail_type,
        };
    }

    public function getCategoryDisplayAttribute(): string
    {
        return match ($this->category) {
            'core' => '핵심',
            'addon' => '부가',
            'support' => '지원',
            'limitation' => '제한',
            'advanced' => '고급',
            default => $this->category ?? '기타',
        };
    }

    public function getIconClassAttribute(): string
    {
        if ($this->icon) {
            return $this->icon;
        }

        return match ($this->detail_type) {
            'feature' => 'fas fa-check-circle',
            'limitation' => 'fas fa-times-circle',
            'requirement' => 'fas fa-exclamation-circle',
            'benefit' => 'fas fa-star',
            'support' => 'fas fa-headset',
            'addon' => 'fas fa-plus-circle',
            default => 'fas fa-info-circle',
        };
    }

    public function getColorClassAttribute(): string
    {
        if ($this->color) {
            return $this->color;
        }

        return match ($this->detail_type) {
            'feature' => 'text-green-500',
            'limitation' => 'text-red-500',
            'requirement' => 'text-yellow-500',
            'benefit' => 'text-blue-500',
            'support' => 'text-purple-500',
            'addon' => 'text-indigo-500',
            default => 'text-gray-500',
        };
    }

    // Helper Methods
    public function isFeature(): bool
    {
        return $this->detail_type === 'feature';
    }

    public function isLimitation(): bool
    {
        return $this->detail_type === 'limitation';
    }

    public function isRequirement(): bool
    {
        return $this->detail_type === 'requirement';
    }

    public function isBenefit(): bool
    {
        return $this->detail_type === 'benefit';
    }

    public function hasLink(): bool
    {
        return !empty($this->link_url);
    }

    public function hasTooltip(): bool
    {
        return !empty($this->tooltip);
    }

    public function checkConditions($context = []): bool
    {
        if (!$this->conditions) {
            return true;
        }

        foreach ($this->conditions as $condition) {
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
            'contains' => is_string($contextValue) && str_contains($contextValue, $value),
            'starts_with' => is_string($contextValue) && str_starts_with($contextValue, $value),
            'ends_with' => is_string($contextValue) && str_ends_with($contextValue, $value),
            default => false,
        };
    }

    protected function getBooleanDisplay($value): string
    {
        $boolValue = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($boolValue === null) {
            return $value;
        }

        return $boolValue ? '✓' : '✗';
    }

    protected function getNumberDisplay($value): string
    {
        if (!is_numeric($value)) {
            return $value;
        }

        $number = (float) $value;

        // 정수인 경우
        if ($number == intval($number)) {
            return number_format(intval($number));
        }

        // 소수인 경우
        return number_format($number, 2);
    }

    protected function getJsonDisplay($value): string
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
        }

        if (is_array($value)) {
            return implode(', ', $value);
        }

        return $value;
    }

    // Static Methods
    public static function getDetailTypes(): array
    {
        return [
            'feature' => '기능',
            'limitation' => '제한사항',
            'requirement' => '요구사항',
            'benefit' => '혜택',
            'support' => '지원',
            'addon' => '부가기능',
        ];
    }

    public static function getValueTypes(): array
    {
        return [
            'text' => '텍스트',
            'number' => '숫자',
            'boolean' => '불린',
            'json' => 'JSON',
            'html' => 'HTML',
        ];
    }

    public static function getCategories(): array
    {
        return [
            'core' => '핵심',
            'addon' => '부가',
            'support' => '지원',
            'limitation' => '제한',
            'advanced' => '고급',
        ];
    }
}