<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workspace extends Model
{
    protected $fillable = [
        'name',
        'type',
        'intent',
        'settings',
        'owner_id'
    ];

    protected $casts = [
        'settings' => 'array'
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function plans()
    {
        return $this->hasMany(Plan::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'workspace_user')
            ->withPivot(['role', 'status'])
            ->withTimestamps();
    }


}
