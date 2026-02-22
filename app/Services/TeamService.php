<?php

namespace App\Services;

use App\Repositories\Contracts\TeamRepositoryInterface;

class TeamService extends BaseService
{
    public function __construct(TeamRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function list(int $perPage): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function get(int $id): \App\Models\Team
    {
        return $this->repository->findOrFail($id)->loadMissing('members:id,name,email');
    }

    public function create(array $data): \App\Models\Team
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

    public function addMember(int $teamId, int $userId): void
    {
        $this->transaction(function () use ($teamId, $userId) {
            $team = $this->repository->findOrFail($teamId);
            $team->members()->syncWithoutDetaching([$userId]);
        });
    }

    public function removeMember(int $teamId, int $userId): void
    {
        $this->transaction(function () use ($teamId, $userId) {
            $team = $this->repository->findOrFail($teamId);
            $team->members()->detach($userId);
        });
    }
}
