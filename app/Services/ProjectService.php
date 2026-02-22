<?php

namespace App\Services;

use App\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ProjectService extends BaseService
{
    public function __construct(ProjectRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function list(int $perPage): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function get(int $id): \App\Models\Project
    {
        return $this->repository->findOrFail($id);
    }

    public function create(array $data): \App\Models\Project
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

    public function assignMember(int $projectId, int $userId): void
    {
        $this->transaction(function () use ($projectId, $userId) {
            $project = $this->repository->findOrFail($projectId);
            $project->members()->syncWithoutDetaching([$userId]);
        });
    }

    public function removeMember(int $projectId, int $userId): void
    {
        $this->transaction(function () use ($projectId, $userId) {
            $project = $this->repository->findOrFail($projectId);
            $project->members()->detach($userId);
        });
    }
}
