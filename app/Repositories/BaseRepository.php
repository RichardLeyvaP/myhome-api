<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface BaseRepository
{
    /**
     * Debe implementar un método que devuelva todas las filas
     * @param array $relatedModels Modelos relacionados que deben cargase
     * @return Illuminate\Database\Eloquent\Collection Datos devueltos
     */
    public function all(int $paginate = 0, array $relatedModels = []): mixed;
    public function get(int $id, array $relatedModels = []): ?Model;
    public function store(Request $request): Model;
    public function update(Request $request, Model $model): Model;
    public function delete(Model $model): ?bool;
}
