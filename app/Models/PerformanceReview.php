<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class PerformanceReview extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'reviewer_id',
        'review_period',
        'review_type',
        'overall_rating',
        'status',
        'review_date',
        'next_review_date',
        'strengths',
        'areas_for_improvement',
        'action_plan',
        'manager_comments',
        'employee_comments',
        'is_acknowledged',
        'acknowledged_at',
    ];

    protected $casts = [
        'overall_rating' => 'decimal:2',
        'review_date' => 'date',
        'next_review_date' => 'date',
        'is_acknowledged' => 'boolean',
        'acknowledged_at' => 'datetime',
    ];

    protected $dates = [
        'review_date',
        'next_review_date',
        'acknowledged_at',
    ];

    // Relationships
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function goals(): HasMany
    {
        return $this->hasMany(PerformanceGoal::class);
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(PerformanceFeedback::class);
    }

    // Accessors
    public function getFormattedReviewDateAttribute(): string
    {
        return $this->review_date ? $this->review_date->format('d M Y') : 'N/A';
    }

    public function getFormattedNextReviewDateAttribute(): string
    {
        return $this->next_review_date ? $this->next_review_date->format('d M Y') : 'N/A';
    }

    public function getFormattedOverallRatingAttribute(): string
    {
        return number_format($this->overall_rating, 1) . '/5.0';
    }

    public function getRatingLevelAttribute(): string
    {
        if ($this->overall_rating >= 4.5) {
            return 'Outstanding';
        } elseif ($this->overall_rating >= 4.0) {
            return 'Excellent';
        } elseif ($this->overall_rating >= 3.5) {
            return 'Good';
        } elseif ($this->overall_rating >= 3.0) {
            return 'Satisfactory';
        } elseif ($this->overall_rating >= 2.0) {
            return 'Needs Improvement';
        } else {
            return 'Unsatisfactory';
        }
    }

    public function getRatingColorAttribute(): string
    {
        if ($this->overall_rating >= 4.0) {
            return 'success';
        } elseif ($this->overall_rating >= 3.0) {
            return 'warning';
        } else {
            return 'danger';
        }
    }

    public function getDaysUntilNextReviewAttribute(): int
    {
        if (!$this->next_review_date) {
            return 0;
        }
        return max(0, now()->diffInDays($this->next_review_date, false));
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->next_review_date && $this->next_review_date < now();
    }

    public function getGoalsProgressAttribute(): array
    {
        $goals = $this->goals;
        $totalGoals = $goals->count();
        
        if ($totalGoals === 0) {
            return [
                'completed' => 0,
                'in_progress' => 0,
                'not_started' => 0,
                'overdue' => 0,
                'completion_percentage' => 0,
            ];
        }

        $completed = $goals->where('status', 'completed')->count();
        $inProgress = $goals->where('status', 'in_progress')->count();
        $notStarted = $goals->where('status', 'not_started')->count();
        $overdue = $goals->where('status', 'overdue')->count();

        return [
            'completed' => $completed,
            'in_progress' => $inProgress,
            'not_started' => $notStarted,
            'overdue' => $overdue,
            'completion_percentage' => round(($completed / $totalGoals) * 100, 2),
        ];
    }

    // Methods
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isAcknowledged(): bool
    {
        return $this->is_acknowledged;
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'in_progress']);
    }

    public function canBeCompleted(): bool
    {
        return $this->status === 'in_progress';
    }

    public function canBeApproved(): bool
    {
        return $this->status === 'completed';
    }

    public function canBeAcknowledged(): bool
    {
        return $this->status === 'approved' && !$this->is_acknowledged;
    }

    public function markAsInProgress(): bool
    {
        if (!$this->isDraft()) {
            return false;
        }

        $this->update(['status' => 'in_progress']);
        return true;
    }

    public function markAsCompleted(): bool
    {
        if (!$this->canBeCompleted()) {
            return false;
        }

        $this->update(['status' => 'completed']);
        return true;
    }

    public function approve(): bool
    {
        if (!$this->canBeApproved()) {
            return false;
        }

        $this->update(['status' => 'approved']);
        return true;
    }

    public function acknowledge(): bool
    {
        if (!$this->canBeAcknowledged()) {
            return false;
        }

        $this->update([
            'is_acknowledged' => true,
            'acknowledged_at' => now(),
        ]);

        return true;
    }

    public function calculateOverallRating(): float
    {
        $goals = $this->goals;
        
        if ($goals->isEmpty()) {
            return $this->overall_rating ?? 0;
        }

        $totalWeightage = $goals->sum('weightage');
        $weightedScore = $goals->sum(function ($goal) {
            return ($goal->achievement_percentage / 100) * $goal->weightage;
        });

        if ($totalWeightage > 0) {
            $this->overall_rating = round(($weightedScore / $totalWeightage) * 5, 2);
        }

        return $this->overall_rating;
    }

    public function getFeedbackSummary(): array
    {
        $feedback = $this->feedback;
        
        $summary = [
            'total_feedback' => $feedback->count(),
            'anonymous_feedback' => $feedback->where('is_anonymous', true)->count(),
            'feedback_by_type' => $feedback->groupBy('feedback_type')->map->count(),
            'average_rating' => $feedback->whereNotNull('rating')->avg('rating'),
        ];

        return $summary;
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByReviewType($query, $type)
    {
        return $query->where('review_type', $type);
    }

    public function scopeByPeriod($query, $period)
    {
        return $query->where('review_period', $period);
    }

    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeByReviewer($query, $reviewerId)
    {
        return $query->where('reviewer_id', $reviewerId);
    }

    public function scopeOverdue($query)
    {
        return $query->where('next_review_date', '<', now());
    }

    public function scopeUpcoming($query, $days = 30)
    {
        return $query->where('next_review_date', '>=', now())
            ->where('next_review_date', '<=', now()->addDays($days));
    }

    public function scopeByRating($query, $minRating, $maxRating = null)
    {
        if ($maxRating) {
            return $query->whereBetween('overall_rating', [$minRating, $maxRating]);
        }
        return $query->where('overall_rating', '>=', $minRating);
    }

    public function scopeAcknowledged($query)
    {
        return $query->where('is_acknowledged', true);
    }

    public function scopeNotAcknowledged($query)
    {
        return $query->where('is_acknowledged', false);
    }

    // Static methods
    public static function getStatusDistribution(): array
    {
        return self::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    public static function getReviewTypeDistribution(): array
    {
        return self::selectRaw('review_type, COUNT(*) as count')
            ->groupBy('review_type')
            ->pluck('count', 'review_type')
            ->toArray();
    }

    public static function getAverageRatingByPeriod(string $period): float
    {
        return self::where('review_period', $period)
            ->whereNotNull('overall_rating')
            ->avg('overall_rating') ?? 0;
    }

    public static function getOverdueReviewsCount(): int
    {
        return self::overdue()->count();
    }

    public static function getUpcomingReviewsCount(int $days = 30): int
    {
        return self::upcoming($days)->count();
    }

    public static function getPendingAcknowledgementsCount(): int
    {
        return self::notAcknowledged()->where('status', 'approved')->count();
    }

    public static function getRatingDistribution(): array
    {
        $reviews = self::whereNotNull('overall_rating')->get();
        
        $distribution = [
            '1.0-1.9' => 0,
            '2.0-2.9' => 0,
            '3.0-3.9' => 0,
            '4.0-4.9' => 0,
            '5.0' => 0,
        ];

        foreach ($reviews as $review) {
            $rating = $review->overall_rating;
            
            if ($rating >= 1.0 && $rating < 2.0) {
                $distribution['1.0-1.9']++;
            } elseif ($rating >= 2.0 && $rating < 3.0) {
                $distribution['2.0-2.9']++;
            } elseif ($rating >= 3.0 && $rating < 4.0) {
                $distribution['3.0-3.9']++;
            } elseif ($rating >= 4.0 && $rating < 5.0) {
                $distribution['4.0-4.9']++;
            } else {
                $distribution['5.0']++;
            }
        }

        return $distribution;
    }
} 