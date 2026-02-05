<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleCalendarEvent extends Model
{
    protected $fillable = [
        'task_id',
        'user_id',
        'google_event_id'
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
