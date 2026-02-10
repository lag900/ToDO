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
        'google_calendar_error',
        'google_calendar_scopes_granted',
        'google_token_expires_at',
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

    protected $appends = ['has_google_integration', 'google_calendar_needs_reconnect'];

    public function getHasGoogleIntegrationAttribute()
    {
        try {
            return !empty($this->google_token);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return false;
        }
    }

    public function getGoogleCalendarNeedsReconnectAttribute()
    {
        try {
            // Needs reconnect if integrated but scopes NOT granted OR explicitly has scope error
            return $this->has_google_integration && (!$this->google_calendar_scopes_granted || !empty($this->google_calendar_error));
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return false;
        }
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
            'google_calendar_scopes_granted' => 'boolean',
            'google_token_expires_at' => 'datetime',
            // Store Google tokens encrypted at rest
            'google_token' => 'encrypted',
            'google_refresh_token' => 'encrypted',
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
