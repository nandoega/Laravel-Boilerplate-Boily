<?php

namespace App\Services;

use App\Repositories\Contracts\TimeEntryRepositoryInterface;

class TimeEntryService extends BaseService
{
    public function __construct(TimeEntryRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function list(int $perPage): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
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

    public function weeklyReport(int $userId): \Illuminate\Support\Collection
    {
        $start = now()->startOfWeek()->toDateString();
        $end   = now()->endOfWeek()->toDateString();
        return $this->repository->weeklyReport($userId, $start, $end);
    }

    public function dailyReport(int $userId, ?string $date = null): \Illuminate\Support\Collection
    {
        return $this->repository->dailyReport($userId, $date ?? now()->toDateString());
    }
}
