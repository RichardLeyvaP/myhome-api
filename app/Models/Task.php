<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Task extends Model
{
    use HasFactory, LogsActivity;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            //->logAll() // Puedes usar logOnly para registrar solo ciertos atributos
            ->logOnly([
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
            ])
            ->setDescriptionForEvent(fn(string $eventName) => "Este modelo fue {$eventName}")
            ->logOnlyDirty();
    }

    /**
     * Personaliza la actividad registrada.
     *
     * @param Activity $activity
     * @param string $eventName
     */
    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->properties = $activity->properties->put('model_id', $this->id);
    }

    // Definir el scope para filtrar por fecha de inicio
    public function scopeWhereStartDate($query, $startDate)
    {
        return $query->whereDate('start_date', $startDate);
    }
}
