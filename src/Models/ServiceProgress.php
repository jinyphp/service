<?php

namespace Jiny\Service\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'checklist_id',
        'checklist_item_id',
        'status',
        'started_at',
        'completed_at',
        'quality_score',
        'provider_notes',
        'evidence_type',
        'evidence_data'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'quality_score' => 'decimal:2',
        'evidence_data' => 'array'
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function checklist()
    {
        return $this->belongsTo(ServiceChecklist::class, 'checklist_id');
    }

    // Scopes
    public function scopeForAppointment($query, $appointmentId)
    {
        return $query->where('appointment_id', $appointmentId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    // Accessors
    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    public function getIsInProgressAttribute(): bool
    {
        return $this->status === 'in_progress';
    }

    public function getDurationAttribute()
    {
        if ($this->started_at && $this->completed_at) {
            return $this->started_at->diffInMinutes($this->completed_at);
        }
        return null;
    }

    // Helper Methods
    public function markAsStarted()
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now()
        ]);
    }

    public function markAsCompleted($qualityScore = null, $notes = null, $evidenceData = null)
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'quality_score' => $qualityScore,
            'provider_notes' => $notes,
            'evidence_data' => $evidenceData
        ]);
    }
}