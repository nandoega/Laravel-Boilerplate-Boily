<?php

namespace App\Services;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Repositories\Contracts\TaskRepositoryInterface;

class TaskService extends BaseService
{
    public function __construct(TaskRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function list(int $perPage): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function get(int $id): \App\Models\Task
    {
        return $this->repository->findOrFail($id);
    }

    public function create(array $data): \App\Models\Task
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

    public function updateStatus(int $id, string $status): bool
    {
        return $this->repository->update($id, ['status' => $status]);
    }

    public function updatePriority(int $id, string $priority): bool
    {
        return $this->repository->update($id, ['priority' => $priority]);
    }

    public function assign(int $id, int $userId): bool
    {
        return $this->repository->update($id, ['assignee_id' => $userId]);
    }
}
