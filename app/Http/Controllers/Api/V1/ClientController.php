<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Http\Resources\Client\ClientResource;
use App\Services\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends BaseController
{
    public function __construct(private readonly ClientService $service) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->service->list(
            $this->perPage($request),
            $request->string('search')->toString() ?: null,
            $request->has('is_active') ? $request->boolean('is_active') : null
        );
        return $this->paginated($data);
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = $this->service->create($request->validated());
        return $this->created(new ClientResource($client));
    }

    public function show(int $id): JsonResponse
    {
        return $this->success(new ClientResource($this->service->get($id)));
    }

    public function update(UpdateClientRequest $request, int $id): JsonResponse
    {
        $this->service->update($id, $request->validated());
        return $this->success(new ClientResource($this->service->get($id)));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->noContent();
    }
}
