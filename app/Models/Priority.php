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
        $user = auth()->user(); // O bien, puedes obtener el usuario de alguna otra manera

        if ($user) {
            // Configura el idioma del usuario
            app()->setLocale($user->language);
        }
        $translations = __('priorities.' . $this->code);

        return [
            'name' => $translations['name'] ?? $this->code,
            'description' => $translations['description'] ?? '',
        ];
    }
}
