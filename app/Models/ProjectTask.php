<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'assigned_to',
        'status',
        'priority',
        'due_date',
        'completed_date'
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_date' => 'date'
    ];

    /**
     * Get the project that the task belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user assigned to the task.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope to filter overdue tasks
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now()->toDateString())
                    ->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * Scope to filter due today
     */
    public function scopeDueToday($query)
    {
        return $query->where('due_date', now()->toDateString())
                    ->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * Check if task is overdue
     */
    public function isOverdue()
    {
        return $this->due_date && 
               $this->due_date->isPast() && 
               !in_array($this->status, ['completed', 'cancelled']);
    }

    /**
     * Check if task is due today
     */
    public function isDueToday()
    {
        return $this->due_date && 
               $this->due_date->isToday() && 
               !in_array($this->status, ['completed', 'cancelled']);
    }

    /**
     * Get priority display name
     */
    public function getPriorityDisplayNameAttribute()
    {
        return ucfirst($this->priority);
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayNameAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    /**
     * Get priority badge class for UI
     */
    public function getPriorityBadgeClassAttribute()
    {
        return match($this->priority) {
            'urgent' => 'badge bg-danger',
            'high' => 'badge bg-warning',
            'medium' => 'badge bg-info',
            'low' => 'badge bg-secondary',
            default => 'badge bg-secondary'
        };
    }

    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'completed' => 'badge bg-success',
            'in_progress' => 'badge bg-primary',
            'pending' => 'badge bg-warning',
            'cancelled' => 'badge bg-danger',
            default => 'badge bg-secondary'
        };
    }
}
