<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements RepositoryInterface
{
    public function __construct(
        protected Model $model
    ) {}

    /**
     * Get all records.
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Find a record by ID.
     */
    public function find(int $id): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * Find a record by ID or fail.
     */
    public function findOrFail(int $id): Model
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create a new record.
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update a record.
     */
    public function update(int $id, array $data): Model
    {
        $record = $this->findOrFail($id);
        $record->update($data);

        return $record->fresh();
    }

    /**
     * Delete a record.
     */
    public function delete(int $id): bool
    {
        $record = $this->findOrFail($id);

        return $record->delete();
    }
}
