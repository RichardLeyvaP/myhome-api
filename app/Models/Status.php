<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'color'
    ];

    public function getTranslatedStatus()
    {
        $translations = __('status.' . $this->name);

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
