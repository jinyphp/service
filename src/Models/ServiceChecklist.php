<?php

namespace Jiny\Service\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'name',
        'version',
        'checklist_data',
        'quality_standards',
        'required_evidence',
        'is_active'
    ];

    protected $casts = [
        'checklist_data' => 'array',
        'quality_standards' => 'array',
        'required_evidence' => 'array',
        'is_active' => 'boolean'
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function serviceProgress()
    {
        return $this->hasMany(ServiceProgress::class, 'checklist_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    // Accessors
    public function getChecklistItemsAttribute()
    {
        return $this->checklist_data['items'] ?? [];
    }

    public function getTotalItemsCountAttribute()
    {
        return count($this->getChecklistItemsAttribute());
    }

    // Helper Methods
    public function getChecklistItem($itemId)
    {
        $items = $this->getChecklistItemsAttribute();
        return collect($items)->firstWhere('id', $itemId);
    }

    public function isItemRequired($itemId)
    {
        $item = $this->getChecklistItem($itemId);
        return $item['required'] ?? false;
    }
}