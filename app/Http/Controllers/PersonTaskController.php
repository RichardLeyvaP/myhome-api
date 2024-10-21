<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\PersonTask;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PersonTaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::info(auth()->user()->name.' - Entra a buscar las tareas asociadas a la persona');
        try {
            $personId = auth()->user()->person->id;

            // Obtener todas las tareas asociadas a la persona
            $tasks = Task::with('people') // Cargar relaciones necesarias
                ->whereHas('people', function($query) use ($personId) {
                    $query->where('people.id', $personId);
                })
                ->get();

            return response()->json(['tasks' => $tasks], 200);
        } catch (\Exception $e) {
            Log::info('PersonTaskController->index');
            Log::error($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    public function getPeopleByTask(Request $request)
    {
        Log::info(auth()->user()->name.' - Entra a buscar las personas asociadas a la tarea');
        try {
           // Validación de los datos de entrada
           $validator = Validator::make($request->all(), [
            'task_id' => 'required|exists:tasks,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['msg' => $validator->errors()->all()], 400);
        }
         // Validar que la tarea exista
         $task = Task::with('people')->find($request->task_id);

            if (!$task) {
                return response()->json(['error' => 'TaskNotFound'], 404);
            }

            // Obtener todas las personas asociadas a la tarea
            $people = $task->people; // Relación people ya cargada por el método 'with'

            return response()->json(['people' => $people], 200);
        } catch (\Exception $e) {
            Log::info('PersonTaskController->getPeopleByTask');
            Log::error($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info(auth()->user()->name.' - Asocia una nueva tarea a la persona');
        try {
            // Validación de los datos de entrada
            $validator = Validator::make($request->all(), [
                'task_id' => 'required|exists:tasks,id',
                'person_id' => 'required|exists:people,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }

            $person = Person::find($request->person_id);
            $task = Task::find($request->task_id);

            // Relacionar la tarea con la persona
            $person->tasks()->attach($task->id);

            return response()->json(['msg' => 'PersonTaskStoreOk'], 201);
        } catch (\Exception $e) {
            Log::info('PersonTaskController->store');
            Log::error($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        Log::info(auth()->user()->name.' - Busca una tarea específica asociada a la persona');
        try {
            // Validación de los datos de entrada
            $validator = Validator::make($request->all(), [
                'task_id' => 'required|exists:tasks,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }

            $personId = auth()->user()->person->id;

            // Verificar que la tarea esté asociada a la persona
            $task = Task::where('id', $request->task_id)
                ->whereHas('person', function($query) use ($personId) {
                    $query->where('id', $personId);
                })
                ->with('person') // Cargar relaciones necesarias
                ->first();

            if (!$task) {
                return response()->json(['msg' => 'TaskNotFound'], 404);
            }

            return response()->json(['task' => $task], 200);
        } catch (\Exception $e) {
            Log::error('PersonTaskController->show: '.$e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    public function storePeople(Request $request)
    {
        Log::info(auth()->user()->name.' - Asocia una nueva tarea a las personas');
        try {
            // Validación de los datos de entrada
            $validator = Validator::make($request->all(), [
                'task_id' => 'required|exists:tasks,id',
                'person_id' => 'required|array',
                'person_id.*' => 'exists:people,id',  // Validar que cada id del array existe en la tabla people
            ]);

            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }

            $task = Task::find($request->task_id);

            // Relacionar la tarea con todas las personas de manera eficiente
            $task->people()->attach($request->person_id); // Asocia todas las personas en una sola operación

            return response()->json(['msg' => 'PersonTaskStoreOk'], 201);
        } catch (\Exception $e) {
            Log::info('PersonTaskController->store');
            Log::error($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        Log::info(auth()->user()->name.' - Edita la asociación de una tarea con una persona');
        try {
            // Validación de los datos de entrada
            $validator = Validator::make($request->all(), [
                'task_id' => 'required|exists:tasks,id',
                'person_id' => 'required|exists:people,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }

            $person = Person::find($request->person_id);
            $task = Task::find($request->task_id);

            if (!$person || !$task) {
                return response()->json(['msg' => 'PersonOrTaskNotFound'], 404);
            }

            // Actualizar la relación de la tarea con la persona
            $person->tasks()->syncWithoutDetaching([$task->id]);

            return response()->json(['msg' => 'PersonTaskUpdateOk'], 200);
        } catch (\Exception $e) {
            Log::info('PersonTaskController->update');
            Log::error($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        Log::info(auth()->user()->name . ' - Elimina una tarea asociada a la persona');
        try {
            // Validación de los datos de entrada
            $validator = Validator::make($request->all(), [
                'task_id' => 'required|exists:tasks,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }

            $personId = auth()->user()->person->id;
            $person = Person::find($personId);

            // Verificar si la relación existe
            $relationExists = $person->tasks()->where('task_id', $request->task_id)->exists();
            if (!$relationExists) {
                return response()->json(['msg' => 'RelationNotFound'], 404);
            }

            // Eliminar la relación
            $person->tasks()->detach($request->task_id);

            return response()->json(['msg' => 'PersonTaskDeleted'], 200);
        } catch (\Exception $e) {
            Log::info('PersonTaskController->destroy');
            Log::error($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }
}
