<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\Task\TaskResource;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends BaseController
{
    public function __construct(private readonly TaskService $service) {}

    public function index(Request $request): JsonResponse
    {
        return $this->paginated($this->service->list($this->perPage($request)));
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        return $this->created(new TaskResource($this->service->create($request->validated())));
    }

    public function show(int $id): JsonResponse
    {
        return $this->success(new TaskResource($this->service->get($id)));
    }

    public function update(UpdateTaskRequest $request, int $id): JsonResponse
    {
        $this->service->update($id, $request->validated());
        return $this->success(new TaskResource($this->service->get($id)));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->noContent();
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate(['status' => ['required', 'string', 'in:pending,in_progress,completed,cancelled']]);
        $this->service->updateStatus($id, $request->string('status'));
        return $this->success(null, 'Status updated');
    }

    public function updatePriority(Request $request, int $id): JsonResponse
    {
        $request->validate(['priority' => ['required', 'string', 'in:low,medium,high,urgent']]);
        $this->service->updatePriority($id, $request->string('priority'));
        return $this->success(null, 'Priority updated');
    }

    public function assign(Request $request, int $id): JsonResponse
    {
        $request->validate(['user_id' => ['required', 'integer', 'exists:users,id']]);
        $this->service->assign($id, $request->integer('user_id'));
        return $this->success(null, 'Task assigned');
    }
}
