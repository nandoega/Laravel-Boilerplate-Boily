<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Team\StoreTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Http\Resources\Team\TeamResource;
use App\Services\TeamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends BaseController
{
    public function __construct(private readonly TeamService $service) {}

    public function index(Request $request): JsonResponse
    {
        return $this->paginated($this->service->list($this->perPage($request)));
    }

    public function store(StoreTeamRequest $request): JsonResponse
    {
        return $this->created(new TeamResource($this->service->create($request->validated())));
    }

    public function show(int $id): JsonResponse
    {
        return $this->success(new TeamResource($this->service->get($id)));
    }

    public function update(UpdateTeamRequest $request, int $id): JsonResponse
    {
        $this->service->update($id, $request->validated());
        return $this->success(new TeamResource($this->service->get($id)));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->noContent();
    }

    public function addMember(Request $request, int $id): JsonResponse
    {
        $request->validate(['user_id' => ['required', 'integer', 'exists:users,id']]);
        $this->service->addMember($id, $request->integer('user_id'));
        return $this->success(null, 'Member added');
    }

    public function removeMember(int $id, int $userId): JsonResponse
    {
        $this->service->removeMember($id, $userId);
        return $this->noContent('Member removed');
    }
}
