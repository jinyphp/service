<?php

namespace Jiny\Service\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ServiceProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider_code',
        'status',
        'specializations',
        'service_areas',
        'available_hours',
        'vehicle_info',
        'equipment_owned',
        'emergency_contact',
        'id_verification_status',
        'background_check_status',
        'insurance_verified'
    ];

    protected $casts = [
        'specializations' => 'array',
        'service_areas' => 'array',
        'available_hours' => 'array',
        'vehicle_info' => 'array',
        'equipment_owned' => 'array',
        'emergency_contact' => 'array',
        'insurance_verified' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'provider_id');
    }

    public function serviceTrackings()
    {
        return $this->hasMany(ServiceTracking::class, 'provider_id');
    }

    public function serviceInspections()
    {
        return $this->hasMany(ServiceInspection::class, 'provider_id');
    }

    public function taskAssignments()
    {
        return $this->hasMany(TaskAssignment::class, 'engineer_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeVerified($query)
    {
        return $query->where('id_verification_status', 'verified')
                    ->where('background_check_status', 'passed')
                    ->where('insurance_verified', true);
    }

    // Accessors
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getIsVerifiedAttribute(): bool
    {
        return $this->id_verification_status === 'verified' &&
               $this->background_check_status === 'passed' &&
               $this->insurance_verified;
    }
}