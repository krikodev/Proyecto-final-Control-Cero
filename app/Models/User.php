<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'dni',
        'email',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'is_active' => 'boolean',
        ];
    }

    public function canAccessWeb(): bool
    {
        return (bool) $this->is_active
            && $this->role()
                ->whereIn('slug', ['administrador', 'supervisor'])
                ->exists();
    }

    public function role(): BelongsTo
{
    return $this->belongsTo(Role::class);
}

public function hasPermission(string $permission): bool
{
    if (! $this->is_active || $this->role_id === null) {
        return false;
    }

    return $this->role()
        ->whereHas('permissions', function ($query) use ($permission) {
            $query->where('permissions.slug', $permission);
        })
        ->exists();
}
}
