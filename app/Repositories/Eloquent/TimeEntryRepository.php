<?php

namespace App\Repositories\Eloquent;

use App\Models\TimeEntry;
use App\Repositories\Contracts\TimeEntryRepositoryInterface;

class TimeEntryRepository extends BaseRepository implements TimeEntryRepositoryInterface
{
    protected string $model = TimeEntry::class;
    protected array $defaultColumns = ['id', 'task_id', 'user_id', 'description', 'hours', 'is_billable', 'hourly_rate', 'date', 'created_at'];
    protected array $with = ['task:id,title', 'user:id,name,email'];

    public function weeklyReport(int $userId, string $startDate, string $endDate): \Illuminate\Support\Collection
    {
        $key = $this->cacheKey("weekly:{$userId}:{$startDate}:{$endDate}");
        return $this->remember($key, fn () =>
            TimeEntry::query()
                ->where('user_id', $userId)
                ->whereBetween('date', [$startDate, $endDate])
                ->with(['task:id,title,project_id', 'task.project:id,name'])
                ->select($this->defaultColumns)
                ->get()
        , config('api.cache.ttl', 300));
    }

    public function dailyReport(int $userId, string $date): \Illuminate\Support\Collection
    {
        $key = $this->cacheKey("daily:{$userId}:{$date}");
        return $this->remember($key, fn () =>
            TimeEntry::query()
                ->where('user_id', $userId)
                ->where('date', $date)
                ->with(['task:id,title'])
                ->select($this->defaultColumns)
                ->get()
        );
    }
}
