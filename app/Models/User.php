<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'external_id',
        'external_auth'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
        ];
    }

    /*//Scopes
    public function scopeAdmin(Builder $query): Builder
    {
        return $query->where('role', RoleEnum::ADMIN->value);
    }

    public function scopeUser(Builder $query): Builder
    {
        return $query->where('role', RoleEnum::USER->value);
    }

    //Helpers
    public function isAdmin(): bool
    {
        return $this->role == RoleEnum::ADMIN->value;
    }

    public function isUser(): bool
    {
        return $this->role == RoleEnum::USER->value;
    }*/

    //Relations
}
