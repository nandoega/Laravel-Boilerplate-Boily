<?php

namespace App\Http\Controllers\Api\V1;

use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

abstract class BaseController extends Controller
{
    use ApiResponseTrait;

    /**
     * Resolve pagination params from request.
     * Caps per_page at configured max.
     */
    protected function perPage(Request $request): int
    {
        $default = config('api.pagination.per_page', 15);
        $max     = config('api.pagination.max_per_page', 100);
        $perPage = (int) $request->query('per_page', $default);

        return min(max($perPage, 1), $max);
    }
}
