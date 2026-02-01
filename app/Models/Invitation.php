<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Invitation extends Model
{
    protected $fillable = [
        'email', 
        'inviteable_type', 
        'inviteable_id', 
        'role', 
        'invited_by', 
        'status', 
        'token', 
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function inviteable(): MorphTo
    {
        return $this->morphTo();
    }
    
    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
}
