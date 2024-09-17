<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;

    // Definir la tabla asociada al modelo
    protected $table = 'product_categories';

    // Definir los campos que se pueden asignar en masa
    protected $fillable = [
        'name',
        'description'
    ];

    public function getTranslatedProductCategories()
    {
        $translations = __('productcategory.' . $this->name);

        return [
            'name' => $translations['name'] ?? $this->name,
            'description' => $translations['description'] ?? $this->description,
        ];
    }

    // Definir las relaciones con otros modelos

    /**
     * Obtener los productos asociados a esta categoría.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
