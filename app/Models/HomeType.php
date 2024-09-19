<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'icon', 'description'];

    public function getTranslatedHomeType()
    {
        $translations = __('hometypes.' . $this->name);

        return [
            'name' => $translations['name'] ?? $this->name,
            'description' => $translations['description'] ?? $this->description,
        ];
    }

    public function homes()
    {
        return $this->hasMany(Home::class);
    }
}
