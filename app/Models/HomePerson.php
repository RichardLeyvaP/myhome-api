<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomePerson extends Model
{
    use HasFactory;

    protected $table = 'home_person'; // Nombre de la tabla

    protected $fillable = [
        'home_id',
        'person_id',
        'role_id',
    ];

    // Relaciones si necesitas acceder a los modelos relacionados
    public function home()
    {
        return $this->belongsTo(Home::class);
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
