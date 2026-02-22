<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

/**
 * Standardized JSON API response envelope.
 *
 * Success:  { "data": {...}, "meta": { "success": true, "message": "..." } }
 * Paginated:{ "data": [...], "meta": { "success": true, "pagination": {...} }, "links": {...} }
 * Error:    { "meta": { "success": false, "message": "..." }, "errors": {...} }
 */
trait ApiResponseTrait
{
    /**
     * 200 OK — single resource or simple data
     */
    protected function success(mixed $data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'meta' => [
                'success' => true,
                'message' => $message,
            ],
        ], $code);
    }

    /**
     * 201 Created
     */
    protected function created(mixed $data = null, string $message = 'Created successfully'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    /**
     * 204 No Content
     */
    protected function noContent(string $message = 'Deleted successfully'): JsonResponse
    {
        return response()->json([
            'meta' => ['success' => true, 'message' => $message],
        ], 200);
    }

    /**
     * Paginated response
     */
    protected function paginated(\Illuminate\Pagination\LengthAwarePaginator $paginator, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'success' => true,
                'message' => $message,
                'pagination' => [
                    'total'        => $paginator->total(),
                    'per_page'     => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'total_pages'  => $paginator->lastPage(),
                    'from'         => $paginator->firstItem(),
                    'to'           => $paginator->lastItem(),
                ],
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last'  => $paginator->url($paginator->lastPage()),
                'prev'  => $paginator->previousPageUrl(),
                'next'  => $paginator->nextPageUrl(),
            ],
        ], 200);
    }

    /**
     * 400 Bad Request
     */
    protected function badRequest(string $message = 'Bad request', array $errors = []): JsonResponse
    {
        return $this->error($message, 400, $errors);
    }

    /**
     * 401 Unauthorized
     */
    protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, 401);
    }

    /**
     * 403 Forbidden
     */
    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, 403);
    }

    /**
     * 404 Not Found
     */
    protected function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->error($message, 404);
    }

    /**
     * 422 Unprocessable Entity
     */
    protected function validationError(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->error($message, 422, $errors);
    }

    /**
     * 429 Too Many Requests
     */
    protected function tooManyRequests(string $message = 'Too many requests'): JsonResponse
    {
        return $this->error($message, 429);
    }

    /**
     * 500 Server Error
     */
    protected function serverError(string $message = 'Internal server error'): JsonResponse
    {
        return $this->error($message, 500);
    }

    /**
     * Generic error response
     */
    protected function error(string $message, int $code = 400, array $errors = []): JsonResponse
    {
        $body = [
            'meta' => [
                'success' => false,
                'message' => $message,
            ],
        ];

        if (!empty($errors)) {
            $body['errors'] = $errors;
        }

        return response()->json($body, $code);
    }
}
