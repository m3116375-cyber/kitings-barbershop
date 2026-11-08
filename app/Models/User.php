<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_OWNER = 'owner';
    public const ROLE_PELANGGAN = 'pelanggan';

    protected $fillable = ['name', 'email', 'phone', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    public function isPelanggan(): bool
    {
        return $this->role === self::ROLE_PELANGGAN;
    }

    /** Halaman tujuan setelah login sesuai role. */
    public function homeUrl(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => route('admin.dashboard'),
            self::ROLE_OWNER => route('owner.dashboard'),
            default => route('home'),
        };
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
