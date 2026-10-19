<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable
{
    use Notifiable;

    public const ROLES = [
        'admin' => 'Admin',
        'editor' => 'Editor',
        'viewer' => 'Viewer',
    ];

    public const ROLE_HINTS = [
        'admin' => 'Everything, including user accounts.',
        'editor' => 'Writes, edits and publishes posts and images.',
        'viewer' => 'Can look around the dashboard but not change anything.',
    ];

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
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /** Whether an authenticator app has been set up and confirmed (App\Support\TwoFactor). */
    public function hasTwoFactor(): bool
    {
        return $this->two_factor_confirmed_at !== null && filled($this->two_factor_secret);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function canEdit(): bool
    {
        return in_array($this->role, ['admin', 'editor'], true);
    }

    public function roleLabel(): string
    {
        return self::ROLES[$this->role] ?? ucfirst((string) $this->role);
    }
}
