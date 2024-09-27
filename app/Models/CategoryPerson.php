<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryPerson extends Model
{
    use HasFactory;

    protected $table = 'category_person';

    protected $fillable = ['category_id', 'person_id'];

    // Acceder a la categoría relacionada
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    // Acceder a la persona relacionada
    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }
}
