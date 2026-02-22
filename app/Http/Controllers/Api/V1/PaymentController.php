<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Payment\StorePaymentRequest;
use App\Http\Requests\Payment\UpdatePaymentRequest;
use App\Http\Resources\Payment\PaymentResource;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends BaseController
{
    public function __construct(private readonly PaymentService $service) {}

    public function index(Request $request): JsonResponse
    {
        return $this->paginated($this->service->list($this->perPage($request)));
    }

    public function store(StorePaymentRequest $request): JsonResponse
    {
        return $this->created(new PaymentResource($this->service->create($request->validated())));
    }

    public function show(int $id): JsonResponse
    {
        return $this->success(new PaymentResource($this->service->get($id)));
    }

    public function update(UpdatePaymentRequest $request, int $id): JsonResponse
    {
        $this->service->update($id, $request->validated());
        return $this->success(new PaymentResource($this->service->get($id)));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->noContent();
    }

    public function refund(Request $request, int $id): JsonResponse
    {
        $request->validate(['reason' => ['required', 'string']]);
        $this->service->refund($id, $request->string('reason'));
        return $this->success(new PaymentResource($this->service->get($id)), 'Payment refunded');
    }
}
