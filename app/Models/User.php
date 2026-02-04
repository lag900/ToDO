<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'display_name',
        'has_completed_onboarding',
        'notification_settings',
        'google_token',
        'google_refresh_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_token',
        'google_refresh_token',
    ];

    protected $appends = ['has_google_integration'];

    public function getHasGoogleIntegrationAttribute()
    {
        return !empty($this->google_token);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notification_settings' => 'array',
        ];
    }

    public function workspaces()
    {
        return $this->belongsToMany(Workspace::class, 'workspace_user')
            ->withPivot(['role', 'status'])
            ->withTimestamps();
    }

    public function sharedPlans()
    {
        return $this->belongsToMany(Plan::class, 'plan_user')
            ->withPivot(['role'])
            ->withTimestamps();
    }
}
