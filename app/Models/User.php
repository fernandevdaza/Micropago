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
    ];

    protected $casts = [
        'password' => 'hashed',
        'role' => UserRole::class,
        'date_of_birth' => 'date',
    ];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'driver_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
