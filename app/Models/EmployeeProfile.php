<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date_of_birth',
        'gender',
        'marital_status',
        'nationality',
        'address',
        'phone',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'skills',
        'certifications',
        'work_experience',
        'education',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'skills' => 'array',
        'certifications' => 'array',
        'work_experience' => 'array',
        'education' => 'array',
    ];

    protected $dates = [
        'date_of_birth',
    ];

    /**
     * Get the employee that owns the profile.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the employee's age.
     */
    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    /**
     * Get formatted skills as string.
     */
    public function getSkillsStringAttribute(): string
    {
        if (!$this->skills || !is_array($this->skills)) {
            return '';
        }

        return implode(', ', $this->skills);
    }

    /**
     * Get formatted certifications as string.
     */
    public function getCertificationsStringAttribute(): string
    {
        if (!$this->certifications || !is_array($this->certifications)) {
            return '';
        }

        return implode(', ', array_column($this->certifications, 'name'));
    }

    /**
     * Get total years of work experience.
     */
    public function getTotalWorkExperienceAttribute(): int
    {
        if (!$this->work_experience || !is_array($this->work_experience)) {
            return 0;
        }

        $totalYears = 0;
        foreach ($this->work_experience as $experience) {
            if (isset($experience['start_date']) && isset($experience['end_date'])) {
                $startDate = \Carbon\Carbon::parse($experience['start_date']);
                $endDate = $experience['end_date'] === 'Present' 
                    ? now() 
                    : \Carbon\Carbon::parse($experience['end_date']);
                $totalYears += $startDate->diffInYears($endDate);
            }
        }

        return $totalYears;
    }

    /**
     * Get highest education level.
     */
    public function getHighestEducationAttribute(): ?string
    {
        if (!$this->education || !is_array($this->education)) {
            return null;
        }

        $educationLevels = [
            'PhD' => 7,
            'Masters' => 6,
            'Bachelor' => 5,
            'Diploma' => 4,
            'High School' => 3,
            'Secondary' => 2,
            'Primary' => 1,
        ];

        $highestLevel = null;
        $highestScore = 0;

        foreach ($this->education as $edu) {
            if (isset($edu['degree'])) {
                foreach ($educationLevels as $level => $score) {
                    if (stripos($edu['degree'], $level) !== false && $score > $highestScore) {
                        $highestLevel = $edu['degree'];
                        $highestScore = $score;
                    }
                }
            }
        }

        return $highestLevel;
    }
} 