<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // Definir la tabla asociada al modelo
    protected $table = 'products';

    // Definir los campos que se pueden asignar en masa
    protected $fillable = [
        'name',
        'category_id',
        'status_id',
        'quantity',
        'unit_price',
        'total_price',
        'purchase_date',
        'purchase_place',
        'expiration_date',
        'additional_notes',
        'brand',
        'image'
    ];

    // Definir las relaciones con otros modelos

    /**
     * Obtener la categoría del producto.
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * Obtener el estado del producto.
     */
    public function status()
    {
        return $this->belongsTo(ProductStatus::class, 'status_id');
    }
}
