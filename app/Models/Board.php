<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Board extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'description', 'plan_id', 'user_id', 'status'];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
