<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\EmployeeController;
use App\Http\Controllers\Api\V1\ClientPaymentController;
use App\Http\Controllers\Api\V1\ManagerFundController;
use App\Http\Controllers\Api\V1\ExpenseCategoryController;
use App\Http\Controllers\Api\V1\ExpenseController;
use App\Http\Controllers\Api\V1\ReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    
    // Public Auth
    Route::post('auth/login', [AuthController::class, 'login']);

    // Authenticated Routes
    Route::middleware('auth:sanctum')->group(function () {
        
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);

        // --- Admin Only Routes ---
        Route::middleware('role:admin')->group(function () {
            // Employees
            Route::apiResource('employees', EmployeeController::class);
            
            // Expense Categories
            Route::post('expense-categories', [ExpenseCategoryController::class, 'store']);
            Route::put('expense-categories/{id}', [ExpenseCategoryController::class, 'update']);

            // Projects (Admin full access)
            Route::post('projects', [ProjectController::class, 'store']);
            Route::put('projects/{id}', [ProjectController::class, 'update']);
            Route::patch('projects/{id}/status', [ProjectController::class, 'updateStatus']);

            // Client Payments
            Route::get('projects/{id}/client-payments', [ClientPaymentController::class, 'index']);
            Route::post('projects/{id}/client-payments', [ClientPaymentController::class, 'store']);

            // Manager Funds
            Route::get('projects/{id}/manager-funds', [ManagerFundController::class, 'index']);
            Route::post('projects/{id}/manager-funds', [ManagerFundController::class, 'store']);

            // Dashboard
            Route::get('dashboard', [ReportController::class, 'dashboard']);
        });

        // --- Admin & Project Manager Routes ---
        
        // Projects listing & view
        Route::get('projects', [ProjectController::class, 'index']);
        Route::get('projects/{id}', [ProjectController::class, 'show']);

        // Expense Categories list
        Route::get('expense-categories', [ExpenseCategoryController::class, 'index']);

        // Expenses
        Route::get('projects/{id}/expenses', [ExpenseController::class, 'index']);
        Route::post('projects/{id}/expenses', [ExpenseController::class, 'store']);
        Route::get('expenses/{id}', [ExpenseController::class, 'show']);

        // Reports
        Route::get('projects/{id}/report', [ReportController::class, 'projectReport']);
        Route::get('projects/{id}/ledger', [ReportController::class, 'projectLedger']);

    });
});
