<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\ReportService;
use Illuminate\Http\JsonResponse;

class ReportController extends BaseController
{
    public function __construct(private readonly ReportService $service) {}

    public function projectProfitability(): JsonResponse
    {
        return $this->success($this->service->projectProfitability());
    }

    public function teamProductivity(): JsonResponse
    {
        return $this->success($this->service->teamProductivity());
    }

    public function revenue(): JsonResponse
    {
        return $this->success($this->service->revenue());
    }

    public function cashFlow(): JsonResponse
    {
        return $this->success($this->service->cashFlow());
    }

    public function dashboard(): JsonResponse
    {
        return $this->success($this->service->dashboard());
    }
}
