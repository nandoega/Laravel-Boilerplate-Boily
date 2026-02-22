<?php

namespace App\Repositories\Eloquent;

use App\Models\Project;
use App\Repositories\Contracts\ProjectRepositoryInterface;

class ProjectRepository extends BaseRepository implements ProjectRepositoryInterface
{
    protected string $model = Project::class;
    protected array $defaultColumns = ['id', 'client_id', 'owner_id', 'name', 'status', 'budget', 'start_date', 'end_date', 'created_at'];
    protected array $with = ['client:id,name', 'owner:id,name,email'];
}
