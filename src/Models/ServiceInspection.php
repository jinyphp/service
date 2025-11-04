<?php

namespace Jiny\Service\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ServiceInspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'customer_id',
        'provider_id',
        'inspection_status',
        'overall_rating',
        'quality_ratings',
        'feedback',
        'rejection_reasons',
        'customer_signature',
        'photo_evidence',
        'inspector_notes',
        'deadline',
        'completed_at'
    ];

    protected $casts = [
        'overall_rating' => 'decimal:2',
        'quality_ratings' => 'array',
        'rejection_reasons' => 'array',
        'photo_evidence' => 'array',
        'deadline' => 'datetime',
        'completed_at' => 'datetime'
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function provider()
    {
        return $this->belongsTo(ServiceProvider::class, 'provider_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('inspection_status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('inspection_status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('inspection_status', 'rejected');
    }

    public function scopeOverdue($query)
    {
        return $query->where('deadline', '<', now())
                    ->where('inspection_status', 'pending');
    }

    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeForProvider($query, $providerId)
    {
        return $query->where('provider_id', $providerId);
    }

    // Accessors
    public function getIsApprovedAttribute(): bool
    {
        return $this->inspection_status === 'approved';
    }

    public function getIsRejectedAttribute(): bool
    {
        return $this->inspection_status === 'rejected';
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->deadline && $this->deadline->isPast() && $this->inspection_status === 'pending';
    }

    public function getHasCustomerSignatureAttribute(): bool
    {
        return !empty($this->customer_signature);
    }

    // Helper Methods
    public function approve($rating = null, $feedback = null, $notes = null)
    {
        $this->update([
            'inspection_status' => 'approved',
            'overall_rating' => $rating,
            'feedback' => $feedback,
            'inspector_notes' => $notes,
            'completed_at' => now()
        ]);
    }

    public function reject($reasons = [], $notes = null)
    {
        $this->update([
            'inspection_status' => 'rejected',
            'rejection_reasons' => $reasons,
            'inspector_notes' => $notes,
            'completed_at' => now()
        ]);
    }

    public function addPhotoEvidence($photoData)
    {
        $evidence = $this->photo_evidence ?? [];
        $evidence[] = array_merge($photoData, ['uploaded_at' => now()]);
        $this->update(['photo_evidence' => $evidence]);
    }
}