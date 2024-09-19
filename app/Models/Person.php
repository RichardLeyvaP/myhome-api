<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'birth_date',
        'gender',
        'email',
        'phone',
        'address',
        'image',
        'user_id',
        'age'
    ];

    public function getTranslatedGender()
    {
        $translations = __('gender.' . $this->gender);

        return [
            'gender' => $translations['name'] ?? $this->gender
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
