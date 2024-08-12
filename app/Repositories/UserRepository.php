<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserRepository implements BaseRepository
{
    public function all(int $paginate = 0, array $relatedModels = []): mixed
    {
        $query = User::query();
        if (count($relatedModels)) {
            $query->with($relatedModels);
        }
        return $paginate ? $query->paginate($paginate) : $query->get();
    }

    public function get(int $id, array $relatedModels = []): ?Model
    {
        $user = User::find($id);
        if ($user && count($relatedModels)) {
            $user->load($relatedModels);
        }
        return $user;
    }

    public function store(Request $request): Model
    {
        return User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => $request->status
        ]);
    }

    public function update(Request $request, Model $model): Model
    {
        $model->name =  $request->name;
        $model->email =  $request->email;
        $model->role =  $request->role;
        $model->status =  $request->status;

        if ($request->change_password) {
            $model->password =  Hash::make($request->password);
        }
        $model->save();

        return $model;
    }

    public function delete(Model $model): ?bool
    {
        return $model->delete();
    }
}
