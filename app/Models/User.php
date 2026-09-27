<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'no_hp'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
        ];
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user is a cashier.
     */
    public function isKasir(): bool
    {
        return $this->role === 'kasir';
    }

    /**
     * Get user-friendly role label.
     */
    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'Admin / Owner',
            default => 'Kasir / Petugas',
        };
    }

    /**
     * Get Tailwind badge classes for role.
     */
    public function getRoleBadgeClassAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'bg-purple-50 text-purple-700 border-purple-200',
            default => 'bg-blue-50 text-blue-700 border-blue-200',
        };
    }
}
