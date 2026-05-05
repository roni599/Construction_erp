<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Employee;
use App\Models\ClientPayment;
use App\Models\ManagerFund;
use App\Models\ManagerReturn;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\ProjectFinancialService;
use Illuminate\Http\Request;

class ProjectWebController extends Controller
{
    protected $financialService;

    public function __construct(ProjectFinancialService $financialService)
    {
        $this->financialService = $financialService;
    }

    public function adminIndex(Request $request)
    {
        $query = Project::with('manager')->orderBy('created_at', 'desc');

        if ($request->filled('project_id')) {
            $query->where('id', $request->project_id);
        }
        if ($request->filled('client_name')) {
            $query->where('client_name', 'like', '%' . $request->client_name . '%');
        }
        if ($request->filled('client_phone')) {
            $query->where('client_phone', 'like', '%' . $request->client_phone . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 10);
        $projects = ($perPage === 'all') ? $query->get() : $query->paginate($perPage);
        $allProjects = Project::orderBy('project_name')->get();

        $employees = Employee::whereHas('user', function($q) {
            $q->where('role', 'project_manager');
        })->get();
        $categories = ExpenseCategory::where('is_active', true)->get();

        return view('admin.projects.index', compact('projects', 'allProjects', 'employees', 'categories'));
    }

    public function adminCreate()
    {
        $employees = Employee::whereHas('user', function($q) {
            $q->where('role', 'project_manager');
        })->get();
        return view('admin.projects.create', compact('employees'));
    }

    public function adminStore(Request $request)
    {
        $request->validate([
            'project_name' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'client_phone' => 'nullable|string|max:50',
            'client_email' => 'nullable|email|max:255',
            'location' => 'nullable|string',
            'employee_id' => 'nullable|exists:employees,id',
            'status' => 'required|in:pending,running,completed,hold',
        ]);

        $data = $request->all();
        if (!isset($data['estimated_budget']) || is_null($data['estimated_budget'])) {
            $data['estimated_budget'] = 0;
        }

        Project::create($data);
        return redirect()->route('admin.projects.index')->with('success', 'Project created successfully.');
    }

    public function adminAllExpenses(Request $request)
    {
        $query = Expense::with(['project', 'category', 'employee']);

        if ($request->project_id) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->start_date) {
            $query->whereDate('expense_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('expense_date', '<=', $request->end_date);
        }
        if ($request->invoice_no) {
            $query->where(function($q) use ($request) {
                $q->where('invoice_no', 'like', "%{$request->invoice_no}%")
                  ->orWhere('id', $request->invoice_no);
            });
        }

        $expenses = $query->latest('expense_date')->orderBy('id', 'desc')->paginate($request->per_page ?? 15);
        $projects = Project::where('status', 'running')->orderBy('project_name', 'asc')->get();
        $categories = ExpenseCategory::where('is_active', true)->get();

        return view('admin.projects.all_expenses', compact('expenses', 'projects', 'categories'));
    }

    public function adminAllReturns(Request $request)
    {
        $query = \App\Models\ManagerReturn::with(['project', 'employee', 'receivedBy']);

        if ($request->project_id) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->start_date) {
            $query->whereDate('return_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('return_date', '<=', $request->end_date);
        }
        if ($request->invoice_no) {
            $query->where(function($q) use ($request) {
                $q->where('invoice_no', 'like', "%{$request->invoice_no}%")
                  ->orWhere('id', $request->invoice_no);
            });
        }

        $returns = $query->latest('return_date')->orderBy('id', 'desc')->paginate($request->per_page ?? 15);
        $projects = Project::orderBy('project_name', 'asc')->get();

        return view('admin.projects.all_returns', compact('returns', 'projects'));
    }

    public function adminEdit($id)
    {
        $project = Project::findOrFail($id);
        $employees = Employee::whereHas('user', function($q) {
            $q->where('role', 'project_manager');
        })->get();
        $totalReceived = \App\Models\ClientPayment::where('project_id', $id)->sum('amount');
        return view('admin.projects.edit', compact('project', 'employees', 'totalReceived'));
    }

    public function adminUpdate(Request $request, $id)
    {
        $totalReceived = \App\Models\ClientPayment::where('project_id', $id)->sum('amount');
        
        $request->validate([
            'project_name' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'client_phone' => 'nullable|string|max:50',
            'client_email' => 'nullable|email|max:255',
            'location' => 'nullable|string',
            'estimated_budget' => 'nullable|numeric|min:' . $totalReceived,
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'status' => 'required|in:pending,running,completed,hold',
            'employee_id' => 'nullable|exists:employees,id',
            'description' => 'nullable|string',
        ], [
            'estimated_budget.min' => 'Estimated budget cannot be less than the total amount already received from the client (Tk. ' . number_format($totalReceived, 2) . ').'
        ]);

        $project = Project::findOrFail($id);
        $oldEmployeeId = $project->employee_id;
        $newEmployeeId = $request->employee_id;

        $project->update($request->all());
        
        // If the manager was changed, we need to update related financial records 
        // so the new manager sees the correct balance.
        if ($newEmployeeId && $newEmployeeId != $oldEmployeeId) {
            \App\Models\ManagerFund::where('project_id', $project->id)->update(['employee_id' => $newEmployeeId]);
            \App\Models\Expense::where('project_id', $project->id)->update(['employee_id' => $newEmployeeId]);
            \App\Models\ManagerReturn::where('project_id', $project->id)->update(['employee_id' => $newEmployeeId]);
        }

        return redirect()->route('admin.projects.index')->with('success', 'Project updated successfully.');
    }

    public function adminShow($id)
    {
        $project = Project::with(['manager'])->findOrFail($id);
        $summary = $this->financialService->getProjectSummary($project);
        return view('admin.projects.show', compact('project', 'summary'));
    }

    public function adminLedger(Request $request, $id)
    {
        $project = Project::with(['manager'])->findOrFail($id);
        $summary = $this->financialService->getProjectSummary($project);
        $ledger = $this->financialService->getProjectLedger(
            $project, 
            $request->start_date, 
            $request->end_date,
            $request->invoice_no
        );
        return view('admin.projects.ledger', compact('project', 'summary', 'ledger'));
    }

    public function adminExpenses(Request $request, $id)
    {
        $project = Project::with(['manager'])->findOrFail($id);
        
        $query = Expense::with('category')->where('project_id', $project->id)->orderBy('expense_date', 'desc');

        if ($request->filled('start_date')) {
            $query->whereDate('expense_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('expense_date', '<=', $request->end_date);
        }
        
        if ($request->invoice_no) {
            $query->where(function($q) use ($request) {
                $q->where('invoice_no', 'like', "%{$request->invoice_no}%")
                  ->orWhere('id', $request->invoice_no);
            });
        }

        $expenses = $query->get();
        $summary = $this->financialService->getProjectSummary($project);
        $categories = ExpenseCategory::where('is_active', true)->get();

        return view('admin.projects.expenses', compact('project', 'expenses', 'summary', 'categories'));
    }

    public function storePayment(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        if ($project->status !== 'running') {
            return back()->with('error', 'Payments can only be recorded for active (running) projects.');
        }

        $totalReceived = \App\Models\ClientPayment::where('project_id', $id)->sum('amount');
        $remaining = $project->estimated_budget - $totalReceived;
        
        $request->validate([
            'amount' => 'required|numeric|min:1' . ($project->estimated_budget > 0 ? '|max:' . ($remaining + 0.01) : ''),
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_banking',
        ], [
            'amount.max' => 'The payment amount cannot exceed the remaining budget (Tk. ' . number_format($remaining, 2) . ').'
        ]);

        ClientPayment::create(array_merge($request->all(), [
            'project_id' => $id,
            'recorded_by' => $request->user()->id
        ]));
        return back()->with('success', 'Client payment recorded.');
    }

    public function storeFund(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        if ($project->status !== 'running') {
            return back()->with('error', 'Funds can only be disbursed to active (running) projects.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'fund_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_banking',
        ]);

        ManagerFund::create(array_merge($request->all(), [
            'project_id' => $project->id,
            'employee_id' => $project->employee_id,
            'given_by' => $request->user()->id
        ]));
        return back()->with('success', 'Fund disbursed to manager.');
    }

    public function storeReturn(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $summary = $this->financialService->getProjectSummary($project);
        $currentBalance = $summary['manager_cash_balance'];

        if ($currentBalance <= 0) {
            return back()->with('error', 'No hand cash balance to return for this project.');
        }

        $request->validate([
            'amount' => 'required|numeric|size:' . $currentBalance,
            'return_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_banking',
        ], [
            'amount.size' => 'The return amount must be exactly equal to the current hand cash balance (Tk. ' . number_format($currentBalance, 2) . ').'
        ]);

        ManagerReturn::create([
            'project_id' => $project->id,
            'employee_id' => $project->employee_id,
            'amount' => $request->amount,
            'return_date' => $request->return_date,
            'payment_method' => $request->payment_method,
            'received_by' => $request->user()->id,
            'note' => $request->note ?? 'Full balance return'
        ]);

        return back()->with('success', 'Fund return of Tk. ' . number_format($request->amount, 2) . ' from manager recorded successfully.');
    }

    // --- GLOBAL FINANCIALS --- //

    public function createGlobalPayment(Request $request)
    {
        $projects = Project::all()->map(function($project) {
            $project->total_received = \App\Models\ClientPayment::where('project_id', $project->id)->sum('amount');
            return $project;
        });
        
        $query = ClientPayment::with(['project', 'recordedBy'])->orderBy('payment_date', 'desc');

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('payment_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('payment_date', '<=', $request->end_date);
        }
        if ($request->filled('invoice_no')) {
            $query->where(function($q) use ($request) {
                $q->where('invoice_no', 'like', "%{$request->invoice_no}%")
                  ->orWhere('id', $request->invoice_no);
            });
        }

        $payments = $query->get();

        return view('admin.projects.payments', compact('projects', 'payments'));
    }

    public function storeGlobalPayment(Request $request)
    {
        $project = Project::findOrFail($request->project_id);
        $totalReceived = \App\Models\ClientPayment::where('project_id', $project->id)->sum('amount');
        $remaining = $project->estimated_budget - $totalReceived;

        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'amount' => 'required|numeric|min:1' . ($project->estimated_budget > 0 ? '|max:' . ($remaining + 0.01) : ''),
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_banking',
        ], [
            'amount.max' => 'The payment amount cannot exceed the remaining budget (Tk. ' . number_format($remaining, 2) . ').'
        ]);

        ClientPayment::create(array_merge($request->all(), [
            'recorded_by' => $request->user()->id
        ]));
        return back()->with('success', 'Client payment recorded successfully.');
    }

    public function createGlobalFund(Request $request)
    {
        $projects = Project::with('manager')->get()->map(function($project) {
            $project->total_disbursed = ManagerFund::where('project_id', $project->id)->sum('amount');
            return $project;
        });
        
        $query = ManagerFund::with(['project', 'givenBy'])->orderBy('fund_date', 'desc');

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('fund_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('fund_date', '<=', $request->end_date);
        }
        if ($request->filled('invoice_no')) {
            $query->where(function($q) use ($request) {
                $q->where('invoice_no', 'like', "%{$request->invoice_no}%")
                  ->orWhere('id', $request->invoice_no);
            });
        }

        $funds = $query->get();

        return view('admin.projects.funds', compact('projects', 'funds'));
    }

    public function storeGlobalFund(Request $request)
    {
        $project = Project::findOrFail($request->project_id);

        if ($project->status !== 'running') {
            return back()->with('error', 'Funds can only be disbursed to active (running) projects.');
        }

        $totalDisbursed = ManagerFund::where('project_id', $project->id)->sum('amount');
        $remaining = $project->estimated_budget - $totalDisbursed;

        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'amount' => 'required|numeric|min:1' . ($project->estimated_budget > 0 ? '|max:' . ($remaining + 0.01) : ''),
            'fund_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_banking',
        ], [
            'amount.max' => 'Total funds disbursed cannot exceed the project budget (Remaining: Tk. ' . number_format($remaining, 2) . ').'
        ]);

        ManagerFund::create(array_merge($request->all(), [
            'employee_id' => $project->employee_id,
            'given_by' => $request->user()->id
        ]));
        return back()->with('success', 'Fund disbursed to manager successfully.');
    }

    public function storeGlobalExpense(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date',
            'bill_image' => 'nullable|image|max:5120'
        ]);

        $project = Project::findOrFail($request->project_id);

        if ($project->status !== 'running') {
            return back()->with('error', 'Expenses can only be recorded for active (running) projects.');
        }

        if (!$project->employee_id) {
            return back()->with('error', 'Please assign a project manager to ' . $project->project_name . ' before recording expenses.');
        }

        $summary = $this->financialService->getProjectSummary($project);
        if ($request->amount > $summary['manager_cash_balance']) {
            return back()->with('error', 'Insufficient hand cash for ' . $project->project_name . '! Available: Tk. ' . number_format($summary['manager_cash_balance'], 2));
        }

        $imagePath = null;
        if ($request->hasFile('bill_image')) {
            $imagePath = $request->file('bill_image')->store('receipts', 'public');
        }

        Expense::create([
            'project_id' => $project->id,
            'employee_id' => $project->employee_id,
            'recorded_by' => auth()->id(),
            'expense_category_id' => $request->expense_category_id,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'description' => $request->description,
            'bill_image' => $imagePath,
            'status' => 'approved'
        ]);

        return back()->with('success', 'Expense recorded successfully for ' . $project->project_name);
    }

    public function adminStoreExpense(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        if ($project->status !== 'running') {
            return back()->with('error', 'Expenses can only be recorded for active (running) projects.');
        }

        if (!$project->employee_id) {
            return back()->with('error', 'Please assign a project manager to this project before recording expenses.');
        }

        $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date',
            'bill_image' => 'nullable|image|max:5120'
        ]);

        $summary = $this->financialService->getProjectSummary($project);
        if ($request->amount > $summary['manager_cash_balance']) {
            return back()->with('error', 'Insufficient hand cash! Available: Tk. ' . number_format($summary['manager_cash_balance'], 2));
        }

        $imagePath = null;
        if ($request->hasFile('bill_image')) {
            $imagePath = $request->file('bill_image')->store('receipts', 'public');
        }

        Expense::create([
            'project_id' => $project->id,
            'employee_id' => $project->employee_id,
            'recorded_by' => auth()->id(),
            'expense_category_id' => $request->expense_category_id,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'description' => $request->description,
            'bill_image' => $imagePath,
            'status' => 'approved'
        ]);

        return back()->with('success', 'Expense recorded successfully.');
    }

    public function showPayment($id)
    {
        $payment = ClientPayment::with(['project', 'recordedBy'])->findOrFail($id);
        return view('admin.projects.payments_show', compact('payment'));
    }

    public function editPayment($id)
    {
        $payment = ClientPayment::findOrFail($id);
        $projects = Project::all();
        return view('admin.projects.payments_edit', compact('payment', 'projects'));
    }

    public function updatePayment(Request $request, $id)
    {
        $payment = ClientPayment::findOrFail($id);
        $project = Project::findOrFail($payment->project_id);
        
        $totalReceivedOther = ClientPayment::where('project_id', $project->id)
            ->where('id', '!=', $id)
            ->sum('amount');
        
        $remaining = $project->estimated_budget - $totalReceivedOther;

        $request->validate([
            'amount' => 'required|numeric|min:1' . ($project->estimated_budget > 0 ? '|max:' . ($remaining + 0.01) : ''),
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_banking',
        ], [
            'amount.max' => 'The payment amount cannot exceed the remaining budget (Tk. ' . number_format($remaining, 2) . ').'
        ]);

        $payment->update($request->all());
        return back()->with('success', 'Client payment updated successfully.');
    }

    public function invoicePayment($id)
    {
        $payment = ClientPayment::with(['project', 'recordedBy'])->findOrFail($id);
        $adminInfo = \App\Models\User::where('role', 'admin')->first();
        return view('admin.projects.payments_invoice', compact('payment', 'adminInfo'));
    }

    public function showFund($id)
    {
        $fund = ManagerFund::with(['project', 'givenBy', 'employee'])->findOrFail($id);
        return view('admin.projects.funds_show', compact('fund'));
    }

    public function editFund($id)
    {
        $fund = ManagerFund::findOrFail($id);
        $projects = Project::with('manager')->get();
        return view('admin.projects.funds_edit', compact('fund', 'projects'));
    }

    public function updateFund(Request $request, $id)
    {
        $fund = ManagerFund::findOrFail($id);
        $project = Project::findOrFail($request->project_id);
        
        $totalDisbursedOther = ManagerFund::where('project_id', $project->id)
            ->where('id', '!=', $id)
            ->sum('amount');
        
        $remaining = $project->estimated_budget - $totalDisbursedOther;

        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'amount' => 'required|numeric|min:1' . ($project->estimated_budget > 0 ? '|max:' . ($remaining + 0.01) : ''),
            'fund_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_banking',
        ], [
            'amount.max' => 'Total funds disbursed cannot exceed the project budget (Remaining: Tk. ' . number_format($remaining, 2) . ').'
        ]);

        $fund = ManagerFund::findOrFail($id);
        $project = Project::findOrFail($request->project_id);
        
        $fund->update(array_merge($request->all(), [
            'employee_id' => $project->employee_id
        ]));

        return redirect()->route('admin.projects.funds.create')->with('success', 'Fund disbursement updated successfully.');
    }

    public function invoiceFund($id)
    {
        $fund = ManagerFund::with(['project', 'givenBy', 'employee'])->findOrFail($id);
        $adminInfo = \App\Models\User::where('role', 'admin')->first();
        return view('admin.projects.funds_invoice', compact('fund', 'adminInfo'));
    }

    public function invoiceReturn($id)
    {
        $return = ManagerReturn::with(['project', 'employee', 'receivedBy'])->findOrFail($id);
        $adminInfo = \App\Models\User::where('role', 'admin')->first();
        return view('manager.projects.return_invoice', compact('return', 'adminInfo'));
    }

    public function invoiceExpense($id)
    {
        $expense = Expense::with(['project', 'category', 'employee'])->findOrFail($id);
        $adminInfo = \App\Models\User::where('role', 'admin')->first();
        return view('manager.projects.expenses_invoice', compact('expense', 'adminInfo'));
    }

    // --- MANAGER VIEWS --- //

    public function managerIndex(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $query = Project::where('employee_id', $request->user()->employee_id)->orderBy('created_at', 'desc');
        $projects = ($perPage === 'all') ? $query->get() : $query->paginate($perPage);
        return view('manager.projects.index', compact('projects'));
    }

    public function managerShow(Request $request, $id)
    {
        $project = Project::where('id', $id)
            ->where('employee_id', $request->user()->employee_id)
            ->firstOrFail();
        
        $perPage = $request->get('per_page', 10);
        
        $expenses = ($perPage === 'all') 
            ? Expense::where('project_id', $id)->with('category')->orderBy('expense_date', 'desc')->get()
            : Expense::where('project_id', $id)->with('category')->orderBy('expense_date', 'desc')->paginate($perPage, ['*'], 'expense_page');

        $funds = ($perPage === 'all')
            ? ManagerFund::where('project_id', $id)->orderBy('fund_date', 'desc')->get()
            : ManagerFund::where('project_id', $id)->orderBy('fund_date', 'desc')->paginate($perPage, ['*'], 'fund_page');

        $summary = $this->financialService->getProjectSummary($project, null, null, true);
        $categories = \App\Models\ExpenseCategory::where('is_active', true)->get();
        $admins = \App\Models\User::where('role', 'admin')->get();

        $returns = ($perPage === 'all')
            ? ManagerReturn::where('project_id', $id)->with('receivedBy')->orderBy('return_date', 'desc')->get()
            : ManagerReturn::where('project_id', $id)->with('receivedBy')->orderBy('return_date', 'desc')->paginate($perPage, ['*'], 'return_page');

        // Prepare Unified Ledger Data
        $ledger = collect();

        // 1. Funds Received (Credit)
        $allFunds = ManagerFund::where('project_id', $id)->get();
        foreach ($allFunds as $fund) {
            $ledger->push((object)[
                'id' => $fund->id,
                'date' => $fund->fund_date,
                'type' => 'Fund Received',
                'description' => 'Received from Admin via ' . ucwords(str_replace('_', ' ', $fund->payment_method)),
                'credit' => $fund->amount,
                'debit' => 0,
                'invoice_no' => $fund->invoice_no ?? 'FND-'.$fund->id,
                'invoice_url' => route('shared.funds.invoice', $fund->id),
                'direction' => 'in'
            ]);
        }

        // 2. Expenses (Debit)
        $allExpenses = Expense::where('project_id', $id)
            ->whereHas('recordedBy', function($q) {
                $q->where('role', 'project_manager');
            })
            ->with(['category', 'recordedBy'])->get();
            
        foreach ($allExpenses as $expense) {
            $ledger->push((object)[
                'id' => $expense->id,
                'date' => $expense->expense_date,
                'type' => 'Expense',
                'description' => ($expense->category->name ?? 'General') . ': ' . ($expense->description ?? 'Project Expense'),
                'credit' => 0,
                'debit' => $expense->status === 'approved' ? $expense->amount : 0,
                'amount' => $expense->amount, // Original amount for display
                'status' => $expense->status,
                'invoice_no' => $expense->invoice_no ?? 'EXP-'.$expense->id,
                'invoice_url' => route('shared.expenses.invoice', $expense->id),
                'direction' => 'out'
            ]);
        }

        // 3. Returns (Debit)
        $allReturns = ManagerReturn::where('project_id', $id)->get();
        foreach ($allReturns as $ret) {
            $ledger->push((object)[
                'id' => $ret->id,
                'date' => $ret->return_date,
                'type' => 'Fund Returned',
                'description' => 'Returned to Admin via ' . ucwords(str_replace('_', ' ', $ret->payment_method)),
                'credit' => 0,
                'debit' => $ret->amount,
                'invoice_no' => $ret->invoice_no ?? 'RET-'.$ret->id,
                'invoice_url' => route('shared.returns.invoice', $ret->id),
                'direction' => 'out'
            ]);
        }

        $ledger = $ledger->sortByDesc('date');

        return view('manager.projects.show', compact('project', 'categories', 'summary', 'admins', 'expenses', 'funds', 'returns', 'ledger'));
    }

    public function managerStoreReturn(Request $request, $id)
    {
        $project = Project::where('id', $id)->where('employee_id', $request->user()->employee_id)->firstOrFail();
        $summary = $this->financialService->getProjectSummary($project, null, null, true);
        $currentBalance = $summary['manager_cash_balance'];

        if ($currentBalance <= 0) {
            return back()->with('error', 'No surplus funds to return.');
        }

        $request->validate([
            'amount' => 'required|numeric|size:' . $currentBalance,
            'return_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_banking',
            'received_by' => 'required|exists:users,id'
        ], [
            'amount.size' => 'The return amount must be exactly equal to your current hand cash balance (Tk. ' . number_format($currentBalance, 2) . ').'
        ]);

        ManagerReturn::create([
            'project_id' => $project->id,
            'employee_id' => $project->employee_id,
            'amount' => $request->amount,
            'return_date' => $request->return_date,
            'payment_method' => $request->payment_method,
            'received_by' => $request->received_by,
            'note' => $request->note ?? 'Manager initiated return'
        ]);

        return redirect()->route('manager.dashboard')->with('success', 'Funds returned to admin successfully. Your project balance is now reconciled.');
    }

    public function managerCreateGlobalReturn(Request $request)
    {
        $projects = Project::where('employee_id', $request->user()->employee_id)->get();
        $balances = [];
        foreach($projects as $p) {
            $sum = $this->financialService->getProjectSummary($p, null, null, true);
            $balances[$p->id] = $sum['manager_cash_balance'];
        }
        $admins = \App\Models\User::where('role', 'admin')->get();
        $query = ManagerReturn::where('employee_id', $request->user()->employee_id)
            ->with(['project', 'receivedBy'])
            ->orderBy('return_date', 'desc');

        if ($request->project_id) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->start_date) {
            $query->whereDate('return_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('return_date', '<=', $request->end_date);
        }
        if ($request->invoice_no) {
            $query->where(function($q) use ($request) {
                $q->where('invoice_no', 'like', "%{$request->invoice_no}%")
                  ->orWhere('id', $request->invoice_no);
            });
        }

        $perPage = $request->get('per_page', 10);
        $returns = ($perPage === 'all') ? $query->get() : $query->paginate($perPage);

        return view('manager.projects.returns', compact('projects', 'balances', 'admins', 'returns'));
    }
    public function adminCreateGlobalExpense(Request $request)
    {
        $projects = Project::where('status', 'running')->get();
        $categories = ExpenseCategory::where('is_active', true)->get();
        
        $selectedProject = null;
        $summary = null;
        if ($request->filled('project_id')) {
            $selectedProject = Project::find($request->project_id);
            if ($selectedProject) {
                $summary = $this->financialService->getProjectSummary($selectedProject);
            }
        }

        return view('admin.projects.create_expense', compact('projects', 'categories', 'selectedProject', 'summary'));
    }


    public function managerCreateGlobalExpense(Request $request)
    {
        $projects = Project::where('employee_id', $request->user()->employee_id)->get();
        $categories = ExpenseCategory::where('is_active', true)->get();
        
        $selectedProject = null;
        $summary = null;
        if ($request->filled('project_id')) {
            $selectedProject = Project::where('id', $request->project_id)
                ->where('employee_id', $request->user()->employee_id)
                ->first();
            if ($selectedProject) {
                $summary = $this->financialService->getProjectSummary($selectedProject, null, null, true);
            }
        }

        return view('manager.projects.create_expense', compact('projects', 'categories', 'selectedProject', 'summary'));
    }

    public function managerStoreGlobalExpense(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date',
            'bill_image' => 'nullable|image|max:5120'
        ]);

        $project = Project::where('id', $request->project_id)
            ->where('employee_id', $request->user()->employee_id)
            ->firstOrFail();

        if ($project->status !== 'running') {
            return back()->with('error', 'Expenses can only be recorded for active (running) projects.');
        }

        $summary = $this->financialService->getProjectSummary($project, null, null, true);
        if ($request->amount > $summary['manager_cash_balance']) {
            return back()->with('error', 'Insufficient hand cash for ' . $project->project_name . '! Available: Tk. ' . number_format($summary['manager_cash_balance'], 2));
        }

        $imagePath = null;
        if ($request->hasFile('bill_image')) {
            $imagePath = $request->file('bill_image')->store('receipts', 'public');
        }

        Expense::create([
            'project_id' => $project->id,
            'employee_id' => $project->employee_id,
            'recorded_by' => auth()->id(),
            'expense_category_id' => $request->expense_category_id,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'description' => $request->description,
            'bill_image' => $imagePath,
            'status' => 'pending'
        ]);

        return redirect()->route('manager.projects.index')->with('success', 'Expense recorded successfully for ' . $project->project_name);
    }

    public function managerStoreGlobalReturn(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
        ]);
        $project = Project::where('id', $request->project_id)
            ->where('employee_id', $request->user()->employee_id)
            ->firstOrFail();

        $summary = $this->financialService->getProjectSummary($project, null, null, true);
        $currentBalance = $summary['manager_cash_balance'];

        if ($currentBalance <= 0) {
            return back()->with('error', 'No surplus funds to return.');
        }

        $request->validate([
            'amount' => 'required|numeric|size:' . $currentBalance,
            'return_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_banking',
            'received_by' => 'required|exists:users,id',
        ], [
            'amount.size' => 'The return amount must be exactly equal to your current hand cash balance (Tk. ' . number_format($currentBalance, 2) . ').'
        ]);

        ManagerReturn::create([
            'project_id' => $project->id,
            'employee_id' => $project->employee_id,
            'amount' => $request->amount,
            'return_date' => $request->return_date,
            'payment_method' => $request->payment_method,
            'received_by' => $request->received_by,
            'note' => $request->note ?? 'Manager initiated return'
        ]);

        return redirect()->route('manager.returns.create')->with('success', 'Funds returned to admin successfully. Your project balance is now reconciled.');
    }

    public function managerLedger(Request $request, $id)
    {
        $project = Project::with(['managerFunds', 'expenses.category'])
            ->where('id', $id)
            ->where('employee_id', $request->user()->employee_id)
            ->firstOrFail();
        
        $summary = $this->financialService->getProjectSummary($project, null, null, true);
        $ledger = $this->financialService->getProjectLedger(
            $project,
            $request->start_date,
            $request->end_date,
            $request->invoice_no,
            true
        );
        
        return view('manager.projects.ledger', compact('project', 'summary', 'ledger'));
    }

    public function managerPrintLedger(Request $request, $id)
    {
        $project = Project::with(['managerFunds', 'expenses.category'])
            ->where('id', $id)
            ->where('employee_id', $request->user()->employee_id)
            ->firstOrFail();
        
        $summary = $this->financialService->getProjectSummary($project, null, null, true);
        $ledger = $this->financialService->getProjectLedger(
            $project,
            $request->start_date,
            $request->end_date,
            $request->invoice_no,
            true
        );
        
        return view('manager.projects.print_ledger', compact('project', 'summary', 'ledger'));
    }

    public function managerStoreExpense(Request $request, $id)
    {
        $project = Project::where('id', $id)->where('employee_id', $request->user()->employee_id)->firstOrFail();

        if ($project->status !== 'running') {
            return back()->with('error', 'Expenses can only be recorded for active (running) projects.');
        }
        
        $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date',
            'bill_image' => 'nullable|image|max:5120'
        ]);

        $summary = $this->financialService->getProjectSummary($project, null, null, true);
        if ($request->amount > $summary['manager_cash_balance']) {
            return back()->with('error', 'Insufficient hand cash! You only have Tk. ' . number_format($summary['manager_cash_balance'], 2) . ' available for this project.');
        }

        $imagePath = null;
        if ($request->hasFile('bill_image')) {
            $imagePath = $request->file('bill_image')->store('receipts', 'public');
        }

        Expense::create([
            'project_id' => $project->id,
            'employee_id' => $project->employee_id,
            'recorded_by' => auth()->id(),
            'expense_category_id' => $request->expense_category_id,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'description' => $request->description,
            'bill_image' => $imagePath,
            'status' => 'pending'
        ]);

        return back()->with('success', 'Expense recorded successfully.');
    }

    public function managerFunds(Request $request)
    {
        $query = \App\Models\ManagerFund::where('employee_id', auth()->user()->employee_id)
            ->with(['project', 'givenBy']);

        if ($request->project_id) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->start_date) {
            $query->whereDate('fund_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('fund_date', '<=', $request->end_date);
        }
        if ($request->invoice_no) {
            $query->where(function($q) use ($request) {
                $q->where('invoice_no', 'like', "%{$request->invoice_no}%")
                  ->orWhere('id', $request->invoice_no);
            });
        }

        $funds = $query->latest('fund_date')->orderBy('id', 'desc')->paginate($request->per_page ?? 10);
        $projects = Project::where('employee_id', auth()->user()->employee_id)->orderBy('project_name', 'asc')->get();

        if ($request->ajax()) {
            return view('manager.projects.funds_table', compact('funds'))->render();
        }

        return view('manager.projects.funds', compact('funds', 'projects'));
    }

    public function managerExpenses(Request $request)
    {
        $query = \App\Models\Expense::where('employee_id', auth()->user()->employee_id)
            ->whereHas('recordedBy', function($q) {
                $q->where('role', 'project_manager');
            })
            ->with(['project', 'category']);

        if ($request->project_id) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->start_date) {
            $query->whereDate('expense_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('expense_date', '<=', $request->end_date);
        }
        if ($request->invoice_no) {
            $query->where(function($q) use ($request) {
                $q->where('invoice_no', 'like', "%{$request->invoice_no}%")
                  ->orWhere('id', $request->invoice_no);
            });
        }

        $expenses = $query->latest('expense_date')->orderBy('id', 'desc')->paginate($request->per_page ?? 10);
        $projectsRaw = Project::where('employee_id', auth()->user()->employee_id)->orderBy('project_name', 'asc')->get();
        $categories = \App\Models\ExpenseCategory::orderBy('name', 'asc')->get();

        $projects = $projectsRaw->map(function($project) {
            $summary = $this->financialService->getProjectSummary($project);
            $project->balance = $summary['manager_cash_balance'];
            return $project;
        });

        if ($request->ajax()) {
            return view('manager.projects.expenses_table', compact('expenses'))->render();
        }

        return view('manager.projects.expenses', compact('expenses', 'projects', 'categories'));
    }


    public function editExpense($id)
    {
        $expense = \App\Models\Expense::with(['project', 'category', 'employee'])->findOrFail($id);
        $categories = \App\Models\ExpenseCategory::all();
        return view('admin.projects.edit_expense', compact('expense', 'categories'));
    }

    public function updateExpense(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'description' => 'nullable|string',
        ]);

        $expense = \App\Models\Expense::findOrFail($id);
        $expense->update([
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'expense_category_id' => $request->expense_category_id,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.projects.all_expenses')->with('success', 'Expense updated successfully.');
    }

    public function adminDestroy($id)
    {
        $project = Project::findOrFail($id);

        if ($project->clientPayments()->exists() || $project->managerFunds()->exists() || $project->expenses()->exists()) {
            return back()->with('error', 'Cannot delete project. It has existing transactions.');
        }

        $project->delete();
        return redirect()->route('admin.projects.index')->with('success', 'Project deleted successfully.');
    }

    public function destroyExpense($id)
    {
        $expense = \App\Models\Expense::findOrFail($id);
        $projectName = $expense->project->project_name;
        $amount = $expense->amount;
        
        $expense->delete();

        return back()->with('success', "Expense of Tk. " . number_format($amount, 2) . " for project '{$projectName}' has been deleted. Project balance has been updated.");
    }

    public function adminUpdateExpenseStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected'
        ]);

        $expense = Expense::findOrFail($id);
        $expense->update(['status' => $request->status]);

        $message = $request->status === 'approved' ? 'Expense approved successfully.' : 'Expense rejected successfully.';
        return back()->with('success', $message);
    }
}
