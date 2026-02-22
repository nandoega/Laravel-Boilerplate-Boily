<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\TeamController;
use App\Http\Controllers\Api\V1\TimeEntryController;
use Illuminate\Support\Facades\Route;

Route::get('health', fn () => response()->json(['status' => 'ok', 'timestamp' => now()]));

Route::prefix('v1')->group(function () {

    // Auth (Rate limited to 5/min)
    Route::prefix('auth')->middleware('throttle:auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);
        
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('profile', [AuthController::class, 'profile']);
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('refresh', [AuthController::class, 'refresh']);
        });
    });

    // Protected API Routes
    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        
        // Clients
        Route::apiResource('clients', ClientController::class);

        // Projects
        Route::post('projects/{project}/assign-member', [ProjectController::class, 'assignMember']);
        Route::post('projects/{project}/remove-member', [ProjectController::class, 'removeMember']);
        Route::apiResource('projects', ProjectController::class);

        // Tasks
        Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus']);
        Route::patch('tasks/{task}/priority', [TaskController::class, 'updatePriority']);
        Route::post('tasks/{task}/assign', [TaskController::class, 'assign']);
        Route::apiResource('tasks', TaskController::class);

        // Teams
        Route::post('teams/{team}/members', [TeamController::class, 'addMember']);
        Route::delete('teams/{team}/members/{user}', [TeamController::class, 'removeMember']);
        Route::apiResource('teams', TeamController::class);

        // Invoices
        Route::post('invoices/generate/{project}', [InvoiceController::class, 'generateFromProject']);
        Route::post('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid']);
        Route::apiResource('invoices', InvoiceController::class);

        // Payments
        Route::post('payments/{payment}/refund', [PaymentController::class, 'refund']);
        Route::apiResource('payments', PaymentController::class);

        // Time Entries
        Route::get('time-entries/report/weekly', [TimeEntryController::class, 'weeklyReport']);
        Route::get('time-entries/report/daily', [TimeEntryController::class, 'dailyReport']);
        Route::apiResource('time-entries', TimeEntryController::class);

        // Reports
        Route::prefix('reports')->group(function () {
            Route::get('project-profitability', [ReportController::class, 'projectProfitability']);
            Route::get('team-productivity', [ReportController::class, 'teamProductivity']);
            Route::get('revenue', [ReportController::class, 'revenue']);
            Route::get('cash-flow', [ReportController::class, 'cashFlow']);
            Route::get('dashboard', [ReportController::class, 'dashboard']);
        });
    });
});
