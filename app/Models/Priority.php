<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Priority extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'color',
        'level'
    ];

    public function getTranslatedAttributes()
    {
        $translations = __('priority.' . $this->name);

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
}
