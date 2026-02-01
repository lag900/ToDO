<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['task_id', 'user_id', 'action', 'changes', 'task_name', 'user_email', 'workspace_id'];

    protected $casts = [
        'changes' => 'array'
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
