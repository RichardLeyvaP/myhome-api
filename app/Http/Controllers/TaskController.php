<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Spatie\Activitylog\Models\Activity;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::info(auth()->user()->name.'-'."Entra a buscar las tareas");
        try {
            $tasks = Task::with('parent', 'children')->get()->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'start_date' => $task->start_date,
                    'end_date' => $task->end_date,
                    'priority_id' => $task->priority_id,
                    'status_id' => $task->status_id,
                    'category_id' => $task->category_id,
                    'recurrence' => $task->recurrence,
                    'estimated_time' => $task->estimated_time,
                    'comments' => $task->comments,
                    'attachments' => $task->attachments,
                    'geo_location' => $task->geo_location,
                    'parent_id' => $task->parent_id,
                    'children' => $task->children->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'title' => $child->title,
                            'description' => $child->description,
                            'start_date' => $child->start_date,
                            'end_date' => $child->end_date,
                            'priority_id' => $child->priority_id,
                            'status_id' => $child->status_id,
                            'category_id' => $child->category_id,
                            'recurrence' => $child->recurrence,
                            'estimated_time' => $child->estimated_time,
                            'comments' => $child->comments,
                            'attachments' => $child->attachments,
                            'geo_location' => $child->geo_location,
                            'parent_id' => $child->parent_id,
                        ];
                    }),
                ];
            });
            if ($tasks->isEmpty()) {
                return response()->json(['tasks' => $tasks], 204);
            }
        
            return response()->json(['tasks' => $tasks], 200);
        } catch (\Exception $e) {
            Log::info('TaskController->index');
            Log::info($e);
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info(auth()->user()->name.'-'."Crea una nueva tarea");
        try {
            // Validación de los datos
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'priority_id' => 'required|exists:priorities,id',
                'parent_id' => 'nullable|exists:tasks,id',
                'status_id' => 'required|exists:statuses,id',
                'category_id' => 'required|exists:categories,id',
                'recurrence' => 'nullable|string|max:255',
                'estimated_time' => 'nullable|integer|min:0',
                'comments' => 'nullable|string',
                'attachments' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048', // max:2048 = 2MB
                'geo_location' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }

           // Crear el registro en la base de datos con los datos proporcionados
            $task = Task::create([
                'title' => $request->title,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'priority_id' => $request->priority_id,
                'parent_id' => $request->parent_id,
                'status_id' => $request->status_id,
                'category_id' => $request->category_id,
                'recurrence' => $request->recurrence,
                'estimated_time' => $request->estimated_time,
                'comments' => $request->comments,
                'geo_location' => $request->geo_location,
            ]);

            if ($request->hasFile('attachments')) {
                $filename = $request->file('attachments')->storeAs('tasks', $task->id . '.' . $request->file('attachments')->extension(), 'public');
                 // Actualizar el registro con la ruta del archivo
                $task->update(['attachments' => $filename]);
            } 
            
    
            return response()->json(['msg' => 'TaskStoreOk', 'task' => $task], 201);
        } catch (\Exception $e) {
            Log::info('TaskController->store');
            Log::info($e);
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        Log::info(auth()->user()->name.'-'."Busca una tarea");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:tasks,id'
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            $task = Task::with('parent', 'children')->findOrFail($request->id);
            if (!$task) {
                return response()->json(['msg' => 'TaskNotfound'], 404);
            }

            // Mapear los datos de la tarea y sus relaciones
        $taskData = [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'start_date' => $task->start_date,
            'end_date' => $task->end_date,
            'priority_id' => $task->priority_id,
            'status_id' => $task->status_id,
            'category_id' => $task->category_id,
            'recurrence' => $task->recurrence,
            'estimated_time' => $task->estimated_time,
            'comments' => $task->comments,
            'attachments' => $task->attachments,
            'geo_location' => $task->geo_location,
            'parent_id' => $task->parent_id,
            'parent' => $task->parent ? [
                'id' => $task->parent->id,
                'title' => $task->parent->title,
                'description' => $task->parent->description,
                'start_date' => $task->parent->start_date,
                'end_date' => $task->parent->end_date,
                'priority_id' => $task->parent->priority_id,
                'status_id' => $task->parent->status_id,
                'category_id' => $task->parent->category_id,
                'recurrence' => $task->parent->recurrence,
                'estimated_time' => $task->parent->estimated_time,
                'comments' => $task->parent->comments,
                'attachments' => $task->parent->attachments,
                'geo_location' => $task->parent->geo_location,
                'parent_id' => $task->parent->parent_id,
            ] : null,
            'children' => $task->children->map(function ($child) {
                return [
                    'id' => $child->id,
                    'title' => $child->title,
                    'description' => $child->description,
                    'start_date' => $child->start_date,
                    'end_date' => $child->end_date,
                    'priority_id' => $child->priority_id,
                    'status_id' => $child->status_id,
                    'category_id' => $child->category_id,
                    'recurrence' => $child->recurrence,
                    'estimated_time' => $child->estimated_time,
                    'comments' => $child->comments,
                    'attachments' => $child->attachments,
                    'geo_location' => $child->geo_location,
                    'parent_id' => $child->parent_id,
                ];
            }),
        ];
            return response()->json(['task' => $taskData], 200);
        } catch (\Exception $e) {
            Log::info('TaskController->show');
            Log::info($e);
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        Log::info(auth()->user()->name.'-'."Edita una tarea");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:tasks,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'priority_id' => 'required|exists:priorities,id',
                'parent_id' => 'nullable|exists:tasks,id',
                'status_id' => 'required|exists:statuses,id',
                'category_id' => 'required|exists:categories,id',
                'recurrence' => 'nullable|string|max:255',
                'estimated_time' => 'nullable|integer|min:0',
                'comments' => 'nullable|string',
                'attachments' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048', // max:2048 = 2MB
                'geo_location' => 'nullable|string|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            
            $task = Task::find($request->id);
            if (!$task) {
                return response()->json(['msg' => 'TaskNotfound'], 404);
            }
            $filename = $task->attachments;
            if ($request->hasFile('attachments')){
                // Verificar si el archivo existe y eliminarlo
            if ($task->attachments && Storage::disk('public')->exists($task->attachments)) {
                Storage::disk('public')->delete($task->attachments);
            }
            //if ($category->icon != 'categories/default_profile.jpg') {
                /*$destination = public_path("storage\\" . $task->attachments);
                if (File::exists($destination)) {
                    File::delete($destination);
                }*/
                $filename = $request->file('attachments')->storeAs('tasks', $task->id . '.' . $request->file('attachments')->extension(), 'public');
            }
            $task->update([
                'title' => $request->title,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'priority_id' => $request->priority_id,
                'parent_id' => $request->parent_id,
                'status_id' => $request->status_id,
                'category_id' => $request->category_id,
                'recurrence' => $request->recurrence,
                'estimated_time' => $request->estimated_time,
                'comments' => $request->comments,
                'attachments' => $filename,
                'geo_location' => $request->geo_location,
            ]);
    
            return response()->json(['msg' => 'TaskUpdateOk', 'task' => $task], 200);
        } catch (\Exception $e) {
            Log::info('TaskController->update');
            Log::info($e);
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        Log::info(auth()->user()->name.'-'."Elimina una tarea");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:tasks,id'
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            $task = Task::find($request->id);
            if (!$task) {
                return response()->json(['msg' => 'TaskNotFound'], 404);
            }

            if ($task->attachments && Storage::disk('public')->exists($task->attachments)) {
                Storage::disk('public')->delete($task->attachments);
            }
                /*$destination = public_path("storage\\" . $task->attachments);
                if (File::exists($destination)) {
                    File::delete($destination);
                }*/

            $task->delete();
    
            return response()->json(['msg' => 'TaskDeleteOk'], 200);
        } catch (\Exception $e) {
            Log::info('TaskController->destroy');
            Log::info($e);
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    public function getTaskHistory(Request $request)
    {
        Log::info(auth()->user()->name.'-'."Busca el historial de una tarea");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:tasks,id'
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            // Encuentra la tarea
            $task = Task::findOrFail($request->id);
            if (!$task) {
                return response()->json(['msg' => 'TaskNotFound'], 404);
            }

               // Obtiene el historial de actividades para la tarea
                $activities = Activity::where('subject_type', Task::class)
                ->where('subject_id', $task->id)
                ->orderBy('created_at', 'desc')
                ->get();
    
            return response()->json(['activities' => $activities], 200);
        } catch (\Exception $e) {
            Log::info('TaskController->getTaskHistory');
            Log::info($e);
            return response()->json(['error' => 'ServerError'], 500);
        }
    }
}
