<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Http\Requests\Invoice\UpdateInvoiceRequest;
use App\Http\Resources\Invoice\InvoiceResource;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends BaseController
{
    public function __construct(private readonly InvoiceService $service) {}

    public function index(Request $request): JsonResponse
    {
        return $this->paginated($this->service->list($this->perPage($request)));
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        return $this->created(new InvoiceResource($this->service->create($request->validated())));
    }

    public function show(int $id): JsonResponse
    {
        return $this->success(new InvoiceResource($this->service->get($id)));
    }

    public function update(UpdateInvoiceRequest $request, int $id): JsonResponse
    {
        $this->service->update($id, $request->validated());
        return $this->success(new InvoiceResource($this->service->get($id)));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->noContent();
    }

    public function generateFromProject(int $projectId): JsonResponse
    {
        $invoice = $this->service->generateFromProject($projectId);
        return $this->created(new InvoiceResource($invoice), 'Invoice generated');
    }

    public function markPaid(int $id): JsonResponse
    {
        $this->service->markPaid($id);
        return $this->success(new InvoiceResource($this->service->get($id)), 'Invoice marked as paid');
    }
}
