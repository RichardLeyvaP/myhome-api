<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'birth_date',
        'gender',
        'email',
        'phone',
        'address',
        'image',
        'user_id',
        'age'
    ];

    public function getTranslatedGender()
    {
        $translations = __('gender.' . $this->gender);

        return [
            'gender' => $translations['name'] ?? $this->gender
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function homes()
    {
        return $this->belongsToMany(Home::class, 'home_person')
            ->withPivot('role_id') // Incluye el atributo role_id en la relación
            ->withTimestamps(); // Incluye created_at y updated_at
    }

    // Relación con Role
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'home_person', 'person_id', 'role_id');
    }
}
