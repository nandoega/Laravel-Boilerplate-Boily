<?php

namespace App\Repositories\Eloquent;

use App\Models\Team;
use App\Repositories\Contracts\TeamRepositoryInterface;

class TeamRepository extends BaseRepository implements TeamRepositoryInterface
{
    protected string $model = Team::class;
    protected array $defaultColumns = ['id', 'owner_id', 'name', 'description', 'created_at'];
    protected array $with = ['owner:id,name,email'];
}
