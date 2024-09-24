<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Home extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'created_at',
        'home_type_id',
        'residents',
        'geolocation',
        'timezone',
        'status',
        'image'
    ];

    public function getTranslatedHomeStatus()
    {
        $translations = __('homestatus.' . $this->status);

        return [
            'status' => $translations['name'] ?? $this->status
        ];
    }

    public function homeType()
    {
        return $this->belongsTo(HomeType::class);
    }

    public function people()
    {
        return $this->belongsToMany(Person::class, 'home_person')
            ->withPivot('role_id') // Incluye el atributo role_id en la relación
            ->withTimestamps(); // Incluye created_at y updated_at
    }
}
