<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date_of_birth',
        'gender',
        'marital_status',
        'nationality',
        'blood_group',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'current_address',
        'permanent_address',
        'emergency_address',
        'emergency_contact_alternate',
        'emergency_contact_alternate_phone',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    protected $dates = [
        'date_of_birth',
    ];

    // Relationships
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function familyMembers(): HasMany
    {
        return $this->hasMany(FamilyMember::class);
    }

    // Accessors
    public function getAgeAttribute(): int
    {
        return $this->date_of_birth ? $this->date_of_birth->age : 0;
    }

    public function getFormattedDateOfBirthAttribute(): string
    {
        return $this->date_of_birth ? $this->date_of_birth->format('d M Y') : 'N/A';
    }

    public function getFullAddressAttribute(): string
    {
        return $this->current_address ?? 'N/A';
    }

    public function getEmergencyContactInfoAttribute(): array
    {
        return [
            'name' => $this->emergency_contact_name,
            'phone' => $this->emergency_contact_phone,
            'relationship' => $this->emergency_contact_relationship,
            'alternate' => $this->emergency_contact_alternate,
            'alternate_phone' => $this->emergency_contact_alternate_phone,
        ];
    }

    // Methods
    public function hasEmergencyContact(): bool
    {
        return !empty($this->emergency_contact_name) && !empty($this->emergency_contact_phone);
    }

    public function hasAlternateEmergencyContact(): bool
    {
        return !empty($this->emergency_contact_alternate) && !empty($this->emergency_contact_alternate_phone);
    }

    public function getFamilyMembersCount(): int
    {
        return $this->familyMembers()->count();
    }

    public function getDependentsCount(): int
    {
        return $this->familyMembers()->where('is_dependent', true)->count();
    }

    // Scopes
    public function scopeByGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }

    public function scopeByMaritalStatus($query, $status)
    {
        return $query->where('marital_status', $status);
    }

    public function scopeByBloodGroup($query, $bloodGroup)
    {
        return $query->where('blood_group', $bloodGroup);
    }

    public function scopeByNationality($query, $nationality)
    {
        return $query->where('nationality', $nationality);
    }

    // Static methods
    public static function getGenderDistribution(): array
    {
        return self::selectRaw('gender, COUNT(*) as count')
            ->groupBy('gender')
            ->pluck('count', 'gender')
            ->toArray();
    }

    public static function getMaritalStatusDistribution(): array
    {
        return self::selectRaw('marital_status, COUNT(*) as count')
            ->groupBy('marital_status')
            ->pluck('count', 'marital_status')
            ->toArray();
    }

    public static function getBloodGroupDistribution(): array
    {
        return self::selectRaw('blood_group, COUNT(*) as count')
            ->whereNotNull('blood_group')
            ->groupBy('blood_group')
            ->pluck('count', 'blood_group')
            ->toArray();
    }

    public static function getAgeDistribution(): array
    {
        $profiles = self::with('employee')->get();
        
        $distribution = [
            '18-25' => 0,
            '26-35' => 0,
            '36-45' => 0,
            '46-55' => 0,
            '56+' => 0,
        ];

        foreach ($profiles as $profile) {
            $age = $profile->age;
            
            if ($age >= 18 && $age <= 25) {
                $distribution['18-25']++;
            } elseif ($age >= 26 && $age <= 35) {
                $distribution['26-35']++;
            } elseif ($age >= 36 && $age <= 45) {
                $distribution['36-45']++;
            } elseif ($age >= 46 && $age <= 55) {
                $distribution['46-55']++;
            } else {
                $distribution['56+']++;
            }
        }

        return $distribution;
    }
} 