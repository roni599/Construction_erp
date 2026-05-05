<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\EmployeeWebController;
use App\Http\Controllers\Web\ProjectWebController;
use App\Http\Controllers\Web\ReportWebController;
use App\Http\Controllers\Web\ExpenseCategoryWebController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AuthController::class, 'login'])->name('login.post');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware('auth')->group(function () {

    // --- Shared Invoice Routes (Admin & Manager) ---
    Route::get('invoices/payments/{id}', [ProjectWebController::class, 'invoicePayment'])->name('shared.payments.invoice');
    Route::get('invoices/funds/{id}', [ProjectWebController::class, 'invoiceFund'])->name('shared.funds.invoice');
    Route::get('invoices/expenses/{id}', [ProjectWebController::class, 'invoiceExpense'])->name('shared.expenses.invoice');
    Route::get('invoices/returns/{id}', [ProjectWebController::class, 'invoiceReturn'])->name('shared.returns.invoice');

    // --- Admin Routes ---
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');
        
        // Employees
        Route::get('employees', [EmployeeWebController::class, 'index'])->name('employees.index');
        Route::post('employees', [EmployeeWebController::class, 'store'])->name('employees.store');
        Route::get('employees/{id}/edit', [EmployeeWebController::class, 'edit'])->name('employees.edit');
        Route::put('employees/{id}', [EmployeeWebController::class, 'update'])->name('employees.update');
        Route::delete('employees/{id}', [EmployeeWebController::class, 'destroy'])->name('employees.destroy');
        Route::get('employees/{id}/financials', [EmployeeWebController::class, 'financials'])->name('employees.financials');
        
        // Projects
        Route::get('projects/create', [ProjectWebController::class, 'adminCreate'])->name('projects.create');
        Route::get('projects', [ProjectWebController::class, 'adminIndex'])->name('projects.index');
        Route::post('projects', [ProjectWebController::class, 'adminStore'])->name('projects.store');
        Route::get('projects/{id}', [ProjectWebController::class, 'adminShow'])->name('projects.show');
        Route::get('projects/{id}/ledger', [ProjectWebController::class, 'adminLedger'])->name('projects.ledger');
        Route::get('projects/{id}/expenses', [ProjectWebController::class, 'adminExpenses'])->name('projects.expenses');
        Route::get('projects-expenses/create', [ProjectWebController::class, 'adminCreateGlobalExpense'])->name('projects.expenses.createGlobal');
        Route::get('all-expenses', [ProjectWebController::class, 'adminAllExpenses'])->name('projects.all_expenses');
        Route::get('projects/{id}/edit', [ProjectWebController::class, 'adminEdit'])->name('projects.edit');
        Route::put('projects/{id}', [ProjectWebController::class, 'adminUpdate'])->name('projects.update');
        Route::delete('projects/{id}', [ProjectWebController::class, 'adminDestroy'])->name('projects.destroy');
        
        // Financials
        Route::post('projects/{id}/payments', [ProjectWebController::class, 'storePayment'])->name('projects.payments.store');
        Route::post('projects/{id}/funds', [ProjectWebController::class, 'storeFund'])->name('projects.funds.store');
        Route::post('projects/{id}/returns', [ProjectWebController::class, 'storeReturn'])->name('projects.returns.store');

        Route::get('projects-payments', [ProjectWebController::class, 'createGlobalPayment'])->name('projects.payments.create');
        Route::post('projects-payments', [ProjectWebController::class, 'storeGlobalPayment'])->name('projects.payments.storeGlobal');
        Route::get('projects-payments/{id}', [ProjectWebController::class, 'showPayment'])->name('projects.payments.show');
        Route::get('projects-payments/{id}/edit', [ProjectWebController::class, 'editPayment'])->name('projects.payments.edit');
        Route::put('projects-payments/{id}', [ProjectWebController::class, 'updatePayment'])->name('projects.payments.update');
        Route::get('projects-payments/{id}/invoice', [ProjectWebController::class, 'invoicePayment'])->name('projects.payments.invoice');
        Route::get('all-returns', [ProjectWebController::class, 'adminAllReturns'])->name('projects.all_returns');
        Route::post('projects-expenses', [ProjectWebController::class, 'storeGlobalExpense'])->name('projects.expenses.storeGlobal');
        Route::post('projects/{id}/expenses/store', [ProjectWebController::class, 'adminStoreExpense'])->name('projects.expenses.store');
        Route::get('expenses/{id}/edit', [ProjectWebController::class, 'editExpense'])->name('expenses.edit');
        Route::put('expenses/{id}', [ProjectWebController::class, 'updateExpense'])->name('expenses.update');
        Route::delete('expenses/{id}', [ProjectWebController::class, 'destroyExpense'])->name('expenses.destroy');
        Route::patch('expenses/{id}/status', [ProjectWebController::class, 'adminUpdateExpenseStatus'])->name('expenses.updateStatus');
        
        Route::get('projects-funds', [ProjectWebController::class, 'createGlobalFund'])->name('projects.funds.create');
        Route::post('projects-funds', [ProjectWebController::class, 'storeGlobalFund'])->name('projects.funds.storeGlobal');
        Route::get('projects-funds/{id}', [ProjectWebController::class, 'showFund'])->name('projects.funds.show');
        Route::get('projects-funds/{id}/edit', [ProjectWebController::class, 'editFund'])->name('projects.funds.edit');
        Route::put('projects-funds/{id}', [ProjectWebController::class, 'updateFund'])->name('projects.funds.update');
        Route::get('projects-funds/{id}/invoice', [ProjectWebController::class, 'invoiceFund'])->name('projects.funds.invoice');
        Route::get('projects-returns/{id}/invoice', [ProjectWebController::class, 'invoiceReturn'])->name('projects.returns.invoice');
        Route::get('expenses/{id}/invoice', [ProjectWebController::class, 'invoiceExpense'])->name('projects.expenses.invoice');

        // Reports
        Route::get('reports', [ReportWebController::class, 'index'])->name('reports.index');
        Route::get('reports/all-projects', [ReportWebController::class, 'allProjects'])->name('reports.all_projects');
        Route::get('reports/all-projects/print', [ReportWebController::class, 'allProjectsPrint'])->name('reports.all_projects.print');
        Route::get('reports/client-receive', [ReportWebController::class, 'clientReceive'])->name('reports.client_receive');
        Route::get('reports/fund-transferred', [ReportWebController::class, 'fundTransferred'])->name('reports.fund_transferred');
        Route::get('reports/fund-returned', [ReportWebController::class, 'fundReturned'])->name('reports.fund_returned');
        Route::get('reports/project-expense', [ReportWebController::class, 'projectExpense'])->name('reports.project_expense');
        
        Route::get('reports/generate', [ReportWebController::class, 'generate'])->name('reports.generate');
        Route::get('reports/print', [ReportWebController::class, 'print'])->name('reports.print');

        // Profile
        Route::get('profile', [\App\Http\Controllers\Web\ProfileController::class, 'index'])->name('profile.index');
        Route::post('profile', [\App\Http\Controllers\Web\ProfileController::class, 'update'])->name('profile.update');
        Route::post('profile/password', [\App\Http\Controllers\Web\ProfileController::class, 'updatePassword'])->name('profile.password');
        
        // Expense Categories
        Route::get('categories', [ExpenseCategoryWebController::class, 'index'])->name('categories.index');
        Route::post('categories', [ExpenseCategoryWebController::class, 'store'])->name('categories.store');
        Route::put('categories/{id}', [ExpenseCategoryWebController::class, 'update'])->name('categories.update');
        Route::delete('categories/{id}', [ExpenseCategoryWebController::class, 'destroy'])->name('categories.destroy');
    });

    // --- Manager Routes ---
    Route::middleware('role:project_manager')->prefix('manager')->name('manager.')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'managerDashboard'])->name('dashboard');
        
        // Projects & Expenses
        Route::get('projects', [ProjectWebController::class, 'managerIndex'])->name('projects.index');
        Route::get('projects/{id}', [ProjectWebController::class, 'managerShow'])->name('projects.show');
        Route::get('projects/{id}/ledger', [ProjectWebController::class, 'managerLedger'])->name('projects.ledger');
        Route::get('projects/{id}/ledger/print', [ProjectWebController::class, 'managerPrintLedger'])->name('projects.ledger.print');
        Route::post('projects/{id}/expenses', [ProjectWebController::class, 'managerStoreExpense'])->name('projects.expenses.store');
        Route::post('projects/{id}/returns', [ProjectWebController::class, 'managerStoreReturn'])->name('projects.returns.store');

        // Global Returns
        Route::get('projects-returns', [ProjectWebController::class, 'managerCreateGlobalReturn'])->name('returns.create');
        Route::post('projects-returns', [ProjectWebController::class, 'managerStoreGlobalReturn'])->name('returns.storeGlobal');
        Route::get('funds', [ProjectWebController::class, 'managerFunds'])->name('funds.index');
        Route::get('expenses', [ProjectWebController::class, 'managerExpenses'])->name('expenses.index');
        Route::get('expenses/create', [ProjectWebController::class, 'managerCreateGlobalExpense'])->name('expenses.create');
        Route::post('expenses/store', [ProjectWebController::class, 'managerStoreGlobalExpense'])->name('expenses.storeGlobal');

        // Expense Categories
        Route::get('categories', [ExpenseCategoryWebController::class, 'index'])->name('categories.index');
        Route::post('categories', [ExpenseCategoryWebController::class, 'store'])->name('categories.store');
        Route::put('categories/{id}', [ExpenseCategoryWebController::class, 'update'])->name('categories.update');
        Route::delete('categories/{id}', [ExpenseCategoryWebController::class, 'destroy'])->name('categories.destroy');

        // Reports
        Route::get('reports', [ReportWebController::class, 'managerIndex'])->name('reports.index');
        Route::get('reports/generate', [ReportWebController::class, 'managerGenerate'])->name('reports.generate');
        Route::get('reports/print', [ReportWebController::class, 'managerPrint'])->name('reports.print');
    });
});
