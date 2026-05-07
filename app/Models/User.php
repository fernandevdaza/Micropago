<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'ci',
        'date_of_birth',
        'balance',
        'nfc_card_uid',
        'transport_line_id',
    ];

    protected $casts = [
        'password' => 'hashed',
        'role' => UserRole::class,
        'date_of_birth' => 'date',
    ];

    public function transportLine()
    {
        return $this->belongsTo(TransportLine::class);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'driver_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function isPassenger(): bool
    {
        return $this->role === UserRole::Passenger;
    }

    public function isDriver(): bool
    {
        return $this->role === UserRole::Driver;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function isLineAdmin(): bool
    {
        return $this->role === UserRole::LineAdmin;
    }

    public function isPlatformOperator(): bool
    {
        return $this->isAdmin() || $this->isSuperAdmin();
    }

    public function belongsToLine(?int $transportLineId): bool
    {
        return $this->transport_line_id !== null
            && $transportLineId !== null
            && $this->transport_line_id === $transportLineId;
    }
}
