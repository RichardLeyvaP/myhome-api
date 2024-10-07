<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens/*, LogsActivity*/;

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
        'external_auth',
        'language'
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

    public function person()
    {
        return $this->hasOne(Person::class);
    }

    public function configurations()
    {
        return $this->hasMany(Configuration::class);
    }

    /*public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            //->logAll() // Puedes usar logOnly para registrar solo ciertos atributos
            ->logOnly([
                'name',
                'email',
                'password',
                'external_id',
                'external_auth',
                'language'
            ])
            ->setDescriptionForEvent(fn(string $eventName) => "Este modelo fue {$eventName}")
            ->logOnlyDirty();
    }*/

    /**
     * Personaliza la actividad registrada.
     *
     * @param Activity $activity
     * @param string $eventName
     */
    /*public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->properties = $activity->properties->put('model_id', $this->id);
    }*/

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
