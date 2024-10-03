<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonTask extends Model
{
    use HasFactory;

    protected $table = 'person_task';

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
