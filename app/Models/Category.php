<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'color',
        'icon',
        'type',
        'parent_id',
        'state'
    ];

      /**
     * Relación para obtener la categoría padre.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Relación para obtener las subcategorías (hijas).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function getTranslatedCategories()
    {
        $translations = __('category.' . $this->name);

        return [
            'name' => $translations['name'] ?? $this->name,
            'description' => $translations['description'] ?? $this->description,
        ];
    }

    /**
     * Relación con la tabla 'tasks'.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function people()
    {
        return $this->belongsToMany(Person::class, 'category_person', 'category_id', 'person_id');
    }

    // Definir un scope para filtrar por tipo
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

}
