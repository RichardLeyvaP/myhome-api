<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Person;
use App\Models\Priority;
use App\Models\Status;
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
            $tasks = Task::with('parent', 'children', 'priority', 'status', 'category')
                ->get()
                ->filter(function ($task) {
                    // Solo mostrar tareas que no tienen padre (tareas principales)
                    return $task->parent_id === null;
                })
                ->map(function ($task) {
                    $translatedAttributes = $task->priority->getTranslatedAttributes();
                    $translatedCategoy = $task->category->getTranslatedCategories();
                    $getTranslatedStatus = $task->status->getTranslatedStatus();
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'description' => $task->description,
                        'startDate' => $task->start_date,
                        'endDate' => $task->end_date,
                        'priorityId' => $task->priority_id,
                        'colorPriority' => $task->priority->color,
                        'namePriority' => $translatedAttributes['name'],
                        'statusId' => $task->status_id,
                        'nameStatus' => $getTranslatedStatus['name'],
                        'categoryId' => $task->category_id,
                        'nameCategory' => $translatedCategoy['name'],
                        'iconCategory' => $task->category->icon,
                        'recurrence' => $task->recurrence,
                        'estimatedTime' => $task->estimated_time,
                        'comments' => $task->comments,
                        'attachments' => $task->attachments,
                        'geoLocation' => $task->geo_location,
                        'parentId' => $task->parent_id,
                        //'parent' => $task->parent ? $this->mapParent($task->parent) : null, // Agregar el mapeo del padre
                        'children' => $this->mapChildren($task->children),
                    ];
                });
            if ($tasks->isEmpty()) {
                return response()->json(['tasks' => $tasks], 204);
            }
        
            return response()->json(['tasks' => $tasks], 200);
        } catch (\Exception $e) {
            Log::info('TaskController->index');
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    public function getTaskDate(Request $request)
    {
        //Log::info(auth()->user()->name.'-'."Entra a buscar las tareas dada una fecha");
        try {
                // Validación de los datos
                $validator = Validator::make($request->all(), [
                    'start_date' => 'required|date',
                ]);
    
                if ($validator->fails()) {
                    return response()->json(['msg' => $validator->errors()->all()], 400);
                }
                $tasks = Task::with('parent', 'children', 'priority', 'status', 'category')
                ->whereStartDate($request->start_date)
                ->get();
            
                // Recolectamos todas las IDs de subtareas
                $subtaskIds = $tasks->pluck('children.*.id')->flatten();
                
                // Ahora filtramos para excluir tareas que ya son subtareas
                $filteredTasks = $tasks->filter(function ($task) use ($subtaskIds) {
                    // Mostramos solo tareas principales o tareas que no están en subtareas
                    return $task->parent_id === null || !$subtaskIds->contains($task->id);
                });
                
                // Mapeamos las tareas restantes
                $mappedTasks = $filteredTasks->map(function ($task) {
                    $translatedAttributes = $task->priority->getTranslatedAttributes();
                    $translatedCategoy = $task->category->getTranslatedCategories();
                    $getTranslatedStatus = $task->status->getTranslatedStatus();
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'description' => $task->description,
                        'startDate' => $task->start_date,
                        'endDate' => $task->end_date,
                        'priorityId' => $task->priority_id,
                        'colorPriority' => $task->priority->color,
                        'namePriority' => $translatedAttributes['name'],
                        'statusId' => $task->status_id,
                        'nameStatus' => $getTranslatedStatus['name'],
                        'categoryId' => $task->category_id,
                        'nameCategory' => $translatedCategoy['name'],
                        'iconCategory' => $task->category->icon,
                        'colorCategory' => $task->category->color,
                        'recurrence' => $task->recurrence,
                        'estimatedTime' => $task->estimated_time,
                        'comments' => $task->comments,
                        'attachments' => $task->attachments,
                        'geoLocation' => $task->geo_location,
                        'parentId' => $task->parent_id,
                        'children' => $this->mapChildren($task->children),
                    ];
                })->values();
                if ($mappedTasks->isEmpty()) {
                    return response()->json(['tasks' => $mappedTasks], 204);
                }
        
            return response()->json(['tasks' => $mappedTasks], 200);
        } catch (\Exception $e) {
            Log::info('TaskController->getTaskDate');
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

        /**
     * Función para mapear el padre de una tarea (si existe).
     */
    public function mapParent($parent)
    {
        $translatedAttributes = $parent->priority->getTranslatedAttributes();
        $translatedCategoy = $parent->category->getTranslatedCategories();
        $getTranslatedStatus = $parent->status->getTranslatedStatus();
        return [
            'id' => $parent->id,
            'title' => $parent->title,
            'description' => $parent->description,
            'start_date' => $parent->start_date,
            'end_date' => $parent->end_date,
            'priority_id' => $parent->priority_id,
            'colorPriority' => $parent->priority->color,
            'namePriority' => $translatedAttributes['name'],
            'status_id' => $parent->status_id,
            'nameStatus' => $getTranslatedStatus['name'],
            'category_id' => $parent->category_id,
            'nameCategory' => $translatedCategoy['name'],
            'iconCategory' => $parent->category->icon,
            'recurrence' => $parent->recurrence,
            'estimated_time' => $parent->estimated_time,
            'comments' => $parent->comments,
            'attachments' => $parent->attachments,
            'geo_location' => $parent->geo_location,
            'parent_id' => $parent->parent_id,
            //'parent' => $parent->parent ? $this->mapParent($parent->parent) : null, // Recursión para mapear ancestros
        ];
    }

    /**
     * Función recursiva para mapear subtareas.
     */
    public function mapChildren($children)
    {
        return $children->map(function ($child) {
            $translatedAttributes = $child->priority->getTranslatedAttributes();
            $translatedCategoy = $child->category->getTranslatedCategories();
            $getTranslatedStatus = $child->status->getTranslatedStatus();
            return [
                'id' => $child->id,
                'title' => $child->title,
                'description' => $child->description,
                'startDate' => $child->start_date,
                'endDate' => $child->end_date,
                'priority_id' => $child->priority_id,
                'colorPriority' => $child->priority->color,
                'namePriority' => $translatedAttributes['name'],
                'status_id' => $child->status_id,
                'nameStatus' => $getTranslatedStatus['name'],
                'category_id' => $child->category_id,
                'nameCategory' => $translatedCategoy['name'],
                'iconCategory' => $child->category->icon,
                'colorCategory' => $child->category->color,
                'recurrence' => $child->recurrence,
                'estimatedTime' => $child->estimated_time,
                'comments' => $child->comments,
                'attachments' => $child->attachments,
                'geoLocation' => $child->geo_location,
                'parentId' => $child->parent_id,
                //'parent' => $child->parent ? $this->mapParent($child->parent) : null, // Agregar el mapeo del padre
                'children' => $this->mapChildren($child->children), // Recursión para hijos
            ];
        });
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //Log::info(auth()->user()->name.'-'."Crea una nueva tarea");
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
            
            $filename = 'tasks/default.jpg';
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
                'attachments' => $filename
            ]);

            if ($request->hasFile('attachments')) {
                $filename = $request->file('attachments')->storeAs('tasks', $task->id . '.' . $request->file('attachments')->extension(), 'public');
                 // Actualizar el registro con la ruta del archivo
                $task->update(['attachments' => $filename]);
            } 
            
    
            return response()->json(['msg' => 'TaskStoreOk', 'task' => $task], 201);
        } catch (\Exception $e) {
            Log::info('TaskController->store');
            Log::info($e->getMessage());
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
            $task = Task::where('id', $request->id)->with('parent', 'children', 'priority', 'status', 'category')->first();
            if (!$task) {
                return response()->json(['msg' => 'TaskNotfound'], 204);
            }

            $translatedAttributes = $task->priority->getTranslatedAttributes();
            $translatedCategoy = $task->category->getTranslatedCategories();
            $getTranslatedStatus = $task->status->getTranslatedStatus();
            // Mapear los datos de la tarea y sus relaciones
        $taskData = [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'start_date' => $task->start_date,
            'end_date' => $task->end_date,
            'priorityId' => $task->priority_id,
            'namePriority' => $translatedAttributes['name'],
            'statusId' => $task->status_id,
            'nameStatus' => $getTranslatedStatus['name'],
            'categoryId' => $task->category_id,
            'nameCategory' => $translatedCategoy['name'],
            'iconCategory' => $task->category->icon,
            'recurrence' => $task->recurrence,
            'estimated_time' => $task->estimated_time,
            'comments' => $task->comments,
            'attachments' => $task->attachments,
            'geo_location' => $task->geo_location,
            'parent_id' => $task->parent_id,
            'children' => $this->mapChildren($task->children),
        ];
            return response()->json(['task' => $taskData], 200);
        } catch (\Exception $e) {
            Log::info('TaskController->show');
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        Log::info(auth()->user()->name . '-' . "Edita una tarea");        
        try {
            // Extraer el array de datos desde la request
            $data = $request->all();
            // Validar solo los campos que puedan estar presentes
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:tasks,id',
                'title' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|nullable|string',
                'start_date' => 'sometimes|nullable|date',
                'end_date' => 'sometimes|nullable|date|after_or_equal:start_date',
                'priority_id' => 'sometimes|required|exists:priorities,id',
                'parent_id' => 'sometimes|nullable|exists:tasks,id',
                'status_id' => 'sometimes|required|exists:statuses,id',
                'category_id' => 'sometimes|required|exists:categories,id',
                'recurrence' => 'sometimes|nullable|string|max:255',
                'estimated_time' => 'sometimes|nullable|integer|min:0',
                'comments' => 'sometimes|nullable|string',
                'attachments' => 'sometimes|nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
                'geo_location' => 'sometimes|nullable|string|max:255',
            ]);
            
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }

            // Buscar la tarea
            $task = Task::find($data['id']);
            if (!$task) {
                return response()->json(['msg' => 'TaskNotFound'], 404);
            }

            // Manejo de archivos adjuntos
            $filename = $task->attachments;
            if (isset($data['attachments']) && $request->hasFile('attachments')) {
                if ($task->attachments != "tasks/default.jpg") {
                    if (Storage::disk('public')->exists($task->attachments)) {
                        Storage::disk('public')->delete($task->attachments);
                    }
                }
                // Guardar el nuevo archivo
                $filename = $request->file('attachments')->storeAs(
                    'tasks', 
                    $task->id . '.' . $request->file('attachments')->extension(), 
                    'public'
                );
            }

            // Filtrar los datos para actualizar solo los campos presentes
            $taskData = array_filter([
                'title' => $data['title'] ?? null,
                'description' => $data['description'] ?? null,
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'priority_id' => $data['priority_id'] ?? null,
                'parent_id' => $data['parent_id'] ?? null,
                'status_id' => $data['status_id'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'recurrence' => $data['recurrence'] ?? null,
                'estimated_time' => $data['estimated_time'] ?? null,
                'comments' => $data['comments'] ?? null,
                'attachments' => $filename,
                'geo_location' => $data['geo_location'] ?? null,
            ], fn($value) => !is_null($value)); // Elimina los valores que sean null

            // Actualizar la tarea
            $task->update($taskData);

            return response()->json(['msg' => 'TaskUpdateOk', 'task' => $task], 200);
        } catch (\Exception $e) {
            Log::error('TaskController->update');
            Log::error($e->getMessage());
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

            if ($task->attachments != "tasks/default.jpg")
            {                   
                // Eliminar la imagen asociada si existe
                if ($task->attachments && Storage::disk('public')->exists($task->attachments)) {
                    Storage::disk('public')->delete($task->attachments);
                }
            }    

            $task->delete();
    
            return response()->json(['msg' => 'TaskDeleteOk'], 200);
        } catch (\Exception $e) {
            Log::info('TaskController->destroy');
            Log::info($e->getMessage());
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
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    public function category_status_priority()
    {
        //Log::info(auth()->user()->name.'-'."Entra a ruta unificada(category_status_priority) buscar las categorias a estados y prioridades");
        try {
            /*$userId = auth()->user()->id;
            $person = Person::where('user_id', $userId)->first();
            if (!$person) {
                return response()->json(['error' => 'Persona no encontrada'], 404);
            }
            $personId = $person->id;
            // Obtener todas las categorías que estén relacionadas con la persona
            $categories1 = Category::with('parent', 'children', 'people')->ofType('Task') // Cargar relaciones necesarias
                ->get()
                ->filter(function ($category) use ($personId) {
                    // Filtrar las categorías que están relacionadas con la persona o tienen state = 1
                    return $category->people->contains('id', $personId) || $category->state == 1;
                })
                ->map(function ($category) use ($personId){
                    if ($category->state == 1) {
                        $translatedAttributes = $category->getTranslatedCategories();
                        $name = $translatedAttributes['name'];
                        $description = $translatedAttributes['description'];
                    } else {
                        $name = $category->name;
                        $description = $category->description;
                    }
                    return [
                        'id' => $category->id,
                        'name' => $name,
                        'description' => $description,
                        'color' => $category->color,
                        'icon' => $category->icon,
                        'parent_id' => $category->parent_id,
                        'children' => $this->mapChildrenCategory1($category->children, $personId),
                    ];
                });*/

            $categories = Category::with('parent', 'children')->ofType('Task')
            ->get()
            ->filter(function ($category) {
                // Solo mostrar categorías que no tienen padre (categorías principales)
                return $category->parent_id === null;
            })
            ->map(function ($category) {
                $translatedCategory = $category->getTranslatedCategories();
                return [
                    'id' => $category->id,
                    'nameCategory' => $translatedCategory['name'],
                    'descriptionCategory' => $translatedCategory['description'],
                    'colorCategory' => $category->color,
                    'iconCategory' => $category->icon,                    
                    'parent_id' => $category->parent_id,
                    //'parent' => $category->parent ? $this->mapParent($category->parent) : null,
                    'children' => $this->mapChildrenCategory($category->children),
                ];
            })->Values();

            //Estados
            $status = Status::ofType('Task')->get()->map(function ($state) {
                $getTranslatedStatus = $state->getTranslatedStatus();
                return [
                    'id' => $state->id,
                    'nameStatus' => $getTranslatedStatus['name'],
                    'descriptionStatus' => $getTranslatedStatus['description'],
                    'colorStatus' => $state->color,
                    'iconStatus' => $state->icon
                ];
            });

            //prioridades
            $priorities = Priority::all()->map(function ($priority) {
                $translatedAttributes = $priority->getTranslatedAttributes();
                return [
                    'id' => $priority->id,
                    'namePriority' => $translatedAttributes['name'],
                    'descriptionPriority' => $translatedAttributes['description'],
                    'colorPriority' => $priority->color,
                    'level' => $priority->level
                ];
            });

            $persons = Person::with(['homes', 'roles'])->get()->map(function ($person) {
                $firstHome = $person->homes->first();
                $role = $firstHome ? $person->roles->where('id', $firstHome->pivot->role_id)->first() : null;
                            //$gettranslatedRoles = $person->role->getTranslatedRoles();
                            return [
                                'id' => $person->id,
                                'namePerson' => $person->name,
                                'imagePerson' => $person->image,
                                'rolId' => $role ? $role->id : null, // Asumiendo que hay un método `name` en Role
                                'nameRole' => $role ? $role->name : 'Sin Rol', // Asumiendo que hay un método `name` en Role
                            ];
                        });
                        
            return response()->json(['taskcategories' => $categories, 'taskstatus' => $status, 'taskpriorities' => $priorities, 'taskpeople' => $persons], 200);
        } catch (\Exception $e) {
            Log::info('CategoryController->index');
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    public function mapChildrenCategory($children)
    {
        return $children->map(function ($child) {
            $translatedCategory = $child->getTranslatedCategories();
            return [
                'id' => $child->id,
                'nameCategory' => $translatedCategory['name'],
                'descriptionCategory' => $translatedCategory['description'],
                'colorCategory' => $child->color,
                'iconCategory' => $child->icon,
                'parent_id' => $child->parent_id,
                'children' => $this->mapChildren($child->children), // Recursión para los hijos de los hijos
            ];
        });
    }

    /*public function mapChildrenCategory1($children, $personId)
    {
        // Filtrar solo los hijos que estén relacionados con la persona o tengan state = 1
        return $children->filter(function ($child) use ($personId) {
            // Verificamos si el hijo está relacionado con la persona o tiene state = 1
            return $child->people->contains('id', $personId) || $child->state == 1;
        })
        ->map(function ($child) use ($personId) {
            if ($child->state == 1) {
                // Obtener los atributos traducidos si el estado es 1
                $translatedAttributes = $child->getTranslatedCategories();
                $name = $translatedAttributes['name'];
                $description = $translatedAttributes['description'];
            } else {
                // Usar los atributos originales si el estado no es 1
                $name = $child->name;
                $description = $child->description;
            }
            return [
                'id' => $child->id,
                'name' => $name,
                'description' => $description,
                'color' => $child->color,
                'icon' => $child->icon,
                'parent_id' => $child->parent_id,
                'children' => $this->mapChildren($child->children, $personId), // Recursividad con personId
            ];
        });
    }*/
}
