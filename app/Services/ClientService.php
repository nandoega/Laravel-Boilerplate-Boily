<?php

namespace App\Services;

use App\Repositories\Contracts\ClientRepositoryInterface;

class ClientService extends BaseService
{
    public function __construct(ClientRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function list(int $perPage, ?string $search = null, ?bool $isActive = null): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->repository->paginateWithFilter($perPage, $search, $isActive);
    }

    public function get(int $id)
    {
        return $this->repository->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
