<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'task_id',
        'sender_id',
        'receiver_id',
        'type',
        'sent_at'
    ];
}
