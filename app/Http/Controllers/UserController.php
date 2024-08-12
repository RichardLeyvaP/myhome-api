<?php

namespace App\Http\Controllers;

use App\Helpers\AppConstants;
use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Administrador', 'Endpoints de Administración')]
#[Subgroup('User', 'Endpoints de Usuarios')]
class UserController extends Controller
{
    public function __construct(
        protected UserRepository $userRepository
    ) {
    }

    #[Endpoint('all', 'Obtiene todos los registros')]
    #[ResponseFromApiResource(UserResource::class, User::class, paginate: AppConstants::PAGE_SIZE)]
    public function index()
    {
        Gate::authorize('viewAny', User::class);

        return UserResource::collection($this->userRepository->all(paginate: AppConstants::PAGE_SIZE));
    }

    #[Endpoint('store', 'Guarda el registro dado')]
    #[BodyParam('password_confirmation', 'string', required: true)]
    #[ResponseFromApiResource(UserResource::class, User::class)]
    public function store(StoreUserRequest $request)
    {
        Gate::authorize('create', User::class);

        return new UserResource($this->userRepository->store($request));
    }

    #[Endpoint('show', 'Obtiene el registro pertinente')]
    #[ResponseFromApiResource(UserResource::class, User::class)]
    public function show(User $user)
    {
        Gate::authorize('view', $user);

        return new UserResource($user);
    }

    #[Endpoint('update', 'Actualiza el registro dado')]
    #[BodyParam('change_password', 'boolean', required: true)]
    #[BodyParam('password_confirmation', 'string', required: false)]
    #[ResponseFromApiResource(UserResource::class, User::class)]
    public function update(UpdateUserRequest $request, User $user)
    {
        Gate::authorize('update', $user);

        return new UserResource($this->userRepository->update($request, $user));
    }

    #[Endpoint('delete', 'Cierra la sesión actual')]
    #[Response(['message' => 'Se ha eliminado correctamente.'])]
    public function destroy(User $user)
    {
        Gate::authorize('delete', $user);

        try {
            $this->userRepository->delete($user);

            return response()->json(['message' => __('messages.deleted')]);
        } catch (\Throwable $th) {
            return response()->json(['message' => __('messages.parent_content_locked')], 400);
        }
    }
}
