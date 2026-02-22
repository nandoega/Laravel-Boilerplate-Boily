<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\TimeEntry\StoreTimeEntryRequest;
use App\Http\Requests\TimeEntry\UpdateTimeEntryRequest;
use App\Http\Resources\TimeEntry\TimeEntryResource;
use App\Services\TimeEntryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimeEntryController extends BaseController
{
    public function __construct(private readonly TimeEntryService $service) {}

    public function index(Request $request): JsonResponse
    {
        return $this->paginated($this->service->list($this->perPage($request)));
    }

    public function store(StoreTimeEntryRequest $request): JsonResponse
    {
        return $this->created(new TimeEntryResource($this->service->create($request->validated())));
    }

    public function show(int $id): JsonResponse
    {
        return $this->success(new TimeEntryResource($this->service->get($id)));
    }

    public function update(UpdateTimeEntryRequest $request, int $id): JsonResponse
    {
        $this->service->update($id, $request->validated());
        return $this->success(new TimeEntryResource($this->service->get($id)));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->noContent();
    }

    public function weeklyReport(Request $request): JsonResponse
    {
        $userId = $request->input('user_id', $request->user()->id);
        return $this->success($this->service->weeklyReport((int) $userId));
    }

    public function dailyReport(Request $request): JsonResponse
    {
        $userId = $request->input('user_id', $request->user()->id);
        return $this->success($this->service->dailyReport((int) $userId, $request->input('date')));
    }
}
