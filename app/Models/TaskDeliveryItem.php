<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskDeliveryItem extends Model
{
    protected $fillable = [
        'task_delivery_id',
        'type',
        'name',
        'content',
        'description'
    ];

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(TaskDelivery::class, 'task_delivery_id');
    }
}
