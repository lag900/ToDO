<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    protected static function boot()
    {
        parent::boot();
    }

    protected $fillable = [
        'title',
        'description',
        'board_id',
        'assigned_to',
        'created_by',
        'parent_id',
        'status',
        'priority',
        'deadline',
        'estimated_minutes',
        'actual_minutes',
        'is_blocked',
        'blocked_reason',
        'start_date',
        'working_by_id',
        'assigned_by_id',
        'project_id'
    ];

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function subtasks()
    {
        return $this->hasMany(Task::class, 'parent_id');
    }

    public function checklists()
    {
        return $this->hasMany(Checklist::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class)->latest();
    }

    public function workingBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'working_by_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_id');
    }

    public function deliveries()
    {
        return $this->hasMany(TaskDelivery::class)->with(['items', 'user'])->latest();
    }
}
