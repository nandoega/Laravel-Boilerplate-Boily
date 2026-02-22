<?php

namespace App\Repositories\Eloquent;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;

class TaskRepository extends BaseRepository implements TaskRepositoryInterface
{
    protected string $model = Task::class;
    protected array $defaultColumns = ['id', 'project_id', 'assignee_id', 'title', 'status', 'priority', 'due_date', 'estimated_hours', 'created_at'];
    protected array $with = ['assignee:id,name,email'];
}
