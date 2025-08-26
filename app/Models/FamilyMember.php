<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_profile_id',
        'name',
        'relationship',
        'date_of_birth',
        'occupation',
        'phone',
        'email',
        'is_dependent',
        'address',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_dependent' => 'boolean',
    ];

    protected $dates = [
        'date_of_birth',
    ];

    // Relationships
    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function employee(): BelongsTo
    {
        return $this->employeeProfile->employee;
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

    public function getFullNameAttribute(): string
    {
        return $this->name ?? 'N/A';
    }

    public function getContactInfoAttribute(): array
    {
        return [
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
        ];
    }

    // Methods
    public function isDependent(): bool
    {
        return $this->is_dependent;
    }

    public function hasContactInfo(): bool
    {
        return !empty($this->phone) || !empty($this->email);
    }

    public function hasAddress(): bool
    {
        return !empty($this->address);
    }

    public function getRelationshipType(): string
    {
        $immediateFamily = ['spouse', 'son', 'daughter', 'father', 'mother'];
        $extendedFamily = ['brother', 'sister', 'grandfather', 'grandmother', 'uncle', 'aunt'];
        
        if (in_array(strtolower($this->relationship), $immediateFamily)) {
            return 'immediate';
        } elseif (in_array(strtolower($this->relationship), $extendedFamily)) {
            return 'extended';
        }
        
        return 'other';
    }

    // Scopes
    public function scopeDependents($query)
    {
        return $query->where('is_dependent', true);
    }

    public function scopeByRelationship($query, $relationship)
    {
        return $query->where('relationship', $relationship);
    }

    public function scopeByOccupation($query, $occupation)
    {
        return $query->where('occupation', $occupation);
    }

    public function scopeHasContactInfo($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('phone')
              ->orWhereNotNull('email');
        });
    }

    // Static methods
    public static function getRelationshipDistribution(): array
    {
        return self::selectRaw('relationship, COUNT(*) as count')
            ->groupBy('relationship')
            ->pluck('count', 'relationship')
            ->toArray();
    }

    public static function getDependentsCount(): int
    {
        return self::where('is_dependent', true)->count();
    }

    public static function getNonDependentsCount(): int
    {
        return self::where('is_dependent', false)->count();
    }

    public static function getFamilySizeDistribution(): array
    {
        $employeeProfiles = EmployeeProfile::withCount('familyMembers')->get();
        
        $distribution = [
            '0' => 0,
            '1-2' => 0,
            '3-4' => 0,
            '5+' => 0,
        ];

        foreach ($employeeProfiles as $profile) {
            $count = $profile->family_members_count;
            
            if ($count === 0) {
                $distribution['0']++;
            } elseif ($count >= 1 && $count <= 2) {
                $distribution['1-2']++;
            } elseif ($count >= 3 && $count <= 4) {
                $distribution['3-4']++;
            } else {
                $distribution['5+']++;
            }
        }

        return $distribution;
    }

    public static function getAverageFamilySize(): float
    {
        return self::count() / EmployeeProfile::count();
    }
} 