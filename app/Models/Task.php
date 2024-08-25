<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'priority_id',
        'parent_id',
        'status_id',
        'category_id',
        'recurrence',
        'estimated_time',
        'comments',
        'attachments',
        'geo_location',
    ];

    /**
     * Relación con la tabla 'priorities'.
     */
    public function priority()
    {
        return $this->belongsTo(Priority::class);
    }

    /**
     * Relación con la tabla 'statuses'.
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * Relación con la tabla 'categories'.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relación para sub-tareas.
     */
    public function parent()
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Task::class, 'parent_id');
    }
}
