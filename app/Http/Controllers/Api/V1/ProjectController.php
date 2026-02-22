<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\Project\ProjectResource;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends BaseController
{
    public function __construct(private readonly ProjectService $service) {}

    public function index(Request $request): JsonResponse
    {
        return $this->paginated($this->service->list($this->perPage($request)));
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        return $this->created(new ProjectResource($this->service->create($request->validated())));
    }

    public function show(int $id): JsonResponse
    {
        return $this->success(new ProjectResource($this->service->get($id)));
    }

    public function update(UpdateProjectRequest $request, int $id): JsonResponse
    {
        $this->service->update($id, $request->validated());
        return $this->success(new ProjectResource($this->service->get($id)));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->noContent();
    }

    public function assignMember(Request $request, int $id): JsonResponse
    {
        $request->validate(['user_id' => ['required', 'integer', 'exists:users,id']]);
        $this->service->assignMember($id, $request->integer('user_id'));
        return $this->success(null, 'Member assigned');
    }

    public function removeMember(Request $request, int $id): JsonResponse
    {
        $request->validate(['user_id' => ['required', 'integer', 'exists:users,id']]);
        $this->service->removeMember($id, $request->integer('user_id'));
        return $this->noContent('Member removed');
    }
}
