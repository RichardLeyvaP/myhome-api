<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStatus extends Model
{
    use HasFactory;

    // Definir la tabla asociada al modelo
    protected $table = 'product_statuses';

    // Definir los campos que se pueden asignar en masa
    protected $fillable = [
        'name',
        'description'
    ];

    public function getTranslatedProductStatus()
    {
        $translations = __('productstatus.' . $this->name);

        return [
            'name' => $translations['name'] ?? $this->name,
            'description' => $translations['description'] ?? $this->description,
        ];
    }

    // Definir las relaciones con otros modelos

    /**
     * Obtener los productos asociados a este estado.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'status_id');
    }
}
