<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use SoftDeletes;

    protected $table = 'plans';

    protected $fillable = ['name', 'description', 'start_date', 'end_date', 'status', 'workspace_id', 'user_id'];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function boards()
    {
        return $this->hasMany(Board::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'plan_user')
            ->withPivot(['role'])
            ->withTimestamps();
    }
}
