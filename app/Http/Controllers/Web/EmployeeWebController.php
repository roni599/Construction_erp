<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class EmployeeWebController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $query = Employee::with('user')->orderBy('created_at', 'desc');
        $employees = ($perPage === 'all') ? $query->get() : $query->paginate($perPage);
        return view('admin.employees.index', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'join_date' => 'nullable|date',
            'picture' => 'nullable|image|max:5120',
            'nid_frontend' => 'nullable|image|max:5120',
            'nid_backend' => 'nullable|image|max:5120',
            'password' => 'required_if:create_user,1|min:6|nullable'
        ]);

        DB::beginTransaction();

        try {
            $picture = $request->file('picture')?->store('employees', 'public');
            $nidFront = $request->file('nid_frontend')?->store('employees/nid', 'public');
            $nidBack = $request->file('nid_backend')?->store('employees/nid', 'public');

            $employee = Employee::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'join_date' => $request->join_date,
                'picture' => $picture,
                'nid_frontend' => $nidFront,
                'nid_backend' => $nidBack,
                'is_active' => true,
            ]);

            if ($request->boolean('create_user')) {
                User::create([
                    'name' => $employee->name,
                    'email' => $employee->email,
                    'password' => Hash::make($request->password),
                    'role' => 'project_manager',
                    'employee_id' => $employee->id,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.employees.index')
                ->with('success', 'Employee created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to create employee: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $employee = Employee::with('user')->findOrFail($id);
        return view('admin.employees.edit', compact('employee'));
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::with('user')->findOrFail($id);

        if ($request->has('password')) {
            $request->validate([
                'password' => 'required|string|min:6'
            ]);

            if ($employee->user) {
                $employee->user->update(['password' => Hash::make($request->password)]);
                return back()->with('success', 'Password reset successfully.');
            } elseif ($request->boolean('create_user')) {
                if (!$employee->email) {
                    return back()->with('error', 'Employee must have an email address to create a login account.');
                }
                User::create([
                    'name' => $employee->name,
                    'email' => $employee->email,
                    'password' => Hash::make($request->password),
                    'role' => 'project_manager',
                    'employee_id' => $employee->id,
                ]);
                return back()->with('success', 'User account created successfully.');
            }
        } else {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|unique:employees,email,' . $employee->id . '|unique:users,email,' . ($employee->user ? $employee->user->id : ''),
                'phone' => 'nullable|string|max:50',
                'address' => 'nullable|string',
                'nid_frontend' => 'nullable|image|max:5120',
                'nid_backend' => 'nullable|image|max:5120',
                'join_date' => 'nullable|date',
                'picture' => 'nullable|image|max:5120',
                'is_active' => 'boolean'
            ]);

            $picturePath = $employee->picture;
            $nidFrontPath = $employee->nid_frontend;
            $nidBackPath = $employee->nid_backend;

            if ($request->hasFile('picture')) {
                $picturePath = $request->file('picture')->store('employees', 'public');
            }
            if ($request->hasFile('nid_frontend')) {
                $nidFrontPath = $request->file('nid_frontend')->store('employees/nid', 'public');
            }
            if ($request->hasFile('nid_backend')) {
                $nidBackPath = $request->file('nid_backend')->store('employees/nid', 'public');
            }

            DB::beginTransaction();
            try {
                $employee->update(array_merge(
                    $request->only(['name', 'email', 'phone', 'address', 'join_date', 'is_active']),
                    [
                        'picture' => $picturePath,
                        'nid_frontend' => $nidFrontPath,
                        'nid_backend' => $nidBackPath,
                    ]
                ));

                // Keep the User email in sync if it exists
                if ($employee->user) {
                    $employee->user->update(['email' => $employee->email, 'name' => $employee->name]);
                }

                DB::commit();
                return back()->with('success', 'Employee details updated successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Error updating employee details.');
            }
        }
    }

    public function financials($id)
    {
        $employee = Employee::with([
            'managerFunds.project',
            'expenses.project',
            'expenses.category',
            'managerReturns.project'
        ])->findOrFail($id);

        $totalFunds = $employee->managerFunds->sum('amount');
        $totalExpenses = $employee->expenses->where('status', 'approved')->sum('amount');
        $totalReturns = $employee->managerReturns->sum('amount');
        $balance = $totalFunds - $totalExpenses - $totalReturns;

        // Group data by project for per-project ledger management
        $projectSummary = [];

        foreach ($employee->managerFunds as $fund) {
            $projectId = $fund->project_id;
            if (!isset($projectSummary[$projectId])) {
                $projectSummary[$projectId] = [
                    'id' => $projectId,
                    'project_name' => $fund->project->project_name ?? 'Unknown Project',
                    'received' => 0,
                    'expenses' => 0,
                    'returns' => 0,
                    'balance' => 0
                ];
            }
            $projectSummary[$projectId]['received'] += $fund->amount;
        }

        foreach ($employee->expenses as $expense) {
            if ($expense->status !== 'approved') continue;
            $projectId = $expense->project_id;
            if (!isset($projectSummary[$projectId])) {
                $projectSummary[$projectId] = [
                    'id' => $projectId,
                    'project_name' => $expense->project->project_name ?? 'Unknown Project',
                    'received' => 0,
                    'expenses' => 0,
                    'returns' => 0,
                    'balance' => 0
                ];
            }
            $projectSummary[$projectId]['expenses'] += $expense->amount;
        }

        foreach ($employee->managerReturns as $return) {
            $projectId = $return->project_id;
            if (!isset($projectSummary[$projectId])) {
                $projectSummary[$projectId] = [
                    'id' => $projectId,
                    'project_name' => $return->project->project_name ?? 'Unknown Project',
                    'received' => 0,
                    'expenses' => 0,
                    'returns' => 0,
                    'balance' => 0
                ];
            }
            $projectSummary[$projectId]['returns'] += $return->amount;
        }

        foreach ($projectSummary as &$summary) {
            $summary['balance'] = $summary['received'] - $summary['expenses'] - ($summary['returns'] ?? 0);
        }

        // Create a unified chronological ledger
        $ledger = collect();

        foreach ($employee->managerFunds as $fund) {
            $ledger->push((object)[
                'id' => $fund->id,
                'type' => 'Fund Received',
                'date' => $fund->fund_date,
                'project' => $fund->project->project_name ?? 'N/A',
                'project_id' => $fund->project_id,
                'description' => 'Fund Received via ' . ucwords(str_replace('_', ' ', $fund->payment_method)),
                'amount' => $fund->amount,
                'invoice_no' => $fund->invoice_no,
                'invoice_url' => route('admin.projects.funds.invoice', $fund->id),
                'direction' => 'in',
                'status' => 'approved'
            ]);
        }

        foreach ($employee->expenses as $expense) {
            $ledger->push((object)[
                'id' => $expense->id,
                'type' => 'Expense',
                'date' => $expense->expense_date,
                'project' => $expense->project->project_name ?? 'N/A',
                'project_id' => $expense->project_id,
                'description' => 'Expense: ' . ($expense->category->name ?? 'General'),
                'amount' => $expense->amount,
                'invoice_no' => $expense->invoice_no,
                'invoice_url' => route('admin.projects.expenses.invoice', $expense->id),
                'direction' => 'out',
                'status' => $expense->status
            ]);
        }

        foreach ($employee->managerReturns as $return) {
            $ledger->push((object)[
                'id' => $return->id,
                'type' => 'Refund',
                'date' => $return->return_date,
                'project' => $return->project->project_name ?? 'N/A',
                'project_id' => $return->project_id,
                'description' => 'Refund to Admin (' . ($return->account->institution_name ?? 'N/A') . ')',
                'amount' => $return->amount,
                'invoice_no' => $return->invoice_no,
                'invoice_url' => route('admin.projects.returns.invoice', $return->id),
                'direction' => 'out',
                'status' => 'approved'
            ]);
        }
        
        // Fetch Admin Expenses for projects managed by this manager
        $projectIds = $employee->projects()->pluck('id');
        $adminExpenses = \App\Models\Expense::whereIn('project_id', $projectIds)
            ->whereHas('recordedBy', function($q) {
                $q->where('role', 'admin');
            })
            ->with(['project', 'category'])
            ->get();

        foreach ($adminExpenses as $expense) {
            $ledger->push((object)[
                'id' => $expense->id,
                'type' => 'Admin Expense',
                'date' => $expense->expense_date,
                'project' => $expense->project->project_name ?? 'N/A',
                'project_id' => $expense->project_id,
                'description' => 'Admin Recorded: ' . ($expense->category->name ?? 'General'),
                'amount' => $expense->amount,
                'invoice_no' => $expense->invoice_no,
                'invoice_url' => route('admin.projects.expenses.invoice', $expense->id),
                'direction' => 'out',
                'status' => $expense->status
            ]);
        }

        $ledger = $ledger->sortByDesc('date');

        return view('admin.employees.financials', compact('employee', 'totalFunds', 'totalExpenses', 'totalReturns', 'balance', 'projectSummary', 'ledger'));
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);

        // Check for dependencies
        if ($employee->projects()->exists()) {
            return back()->with('error', 'Cannot delete employee. They are assigned to active projects.');
        }

        if ($employee->managerFunds()->exists() || $employee->expenses()->exists()) {
            return back()->with('error', 'Cannot delete employee. They have existing financial transactions/records.');
        }

        DB::beginTransaction();
        try {
            // Delete linked user first
            if ($employee->user) {
                $employee->user->delete();
            }
            $employee->delete();

            DB::commit();
            return redirect()->route('admin.employees.index')->with('success', 'Employee and linked account deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error deleting employee.');
        }
    }
}
