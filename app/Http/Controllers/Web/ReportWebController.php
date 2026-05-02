<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ClientPayment;
use App\Models\ManagerFund;
use App\Models\Expense;
use Illuminate\Http\Request;

class ReportWebController extends Controller
{
    protected $financialService;
    
    public function __construct(\App\Services\ProjectFinancialService $financialService)
    {
        $this->financialService = $financialService;
    }

    public function index()
    {
        $projects = Project::all();
        return view('admin.reports.index', compact('projects'));
    }

    public function clientReceive(Request $request)
    {
        $projects = Project::all();
        $query = ClientPayment::with('project');

        if ($request->project_id) $query->where('project_id', $request->project_id);
        if ($request->from_date) $query->where('payment_date', '>=', $request->from_date);
        if ($request->to_date) $query->where('payment_date', '<=', $request->to_date);
        if ($request->invoice_no) {
            $query->where(function($q) use ($request) {
                $q->where('invoice_no', 'like', "%{$request->invoice_no}%")
                  ->orWhere('id', $request->invoice_no);
            });
        }

        $perPage = $request->get('per_page', 10);
        $report_data = ($perPage === 'all') ? $query->orderBy('payment_date', 'desc')->get() : $query->orderBy('payment_date', 'desc')->paginate($perPage);
        return view('admin.reports.client_receive', compact('projects', 'report_data'));
    }

    public function fundTransferred(Request $request)
    {
        $projects = Project::all();
        $query = ManagerFund::with(['project', 'employee']);

        if ($request->project_id) $query->where('project_id', $request->project_id);
        if ($request->from_date) $query->where('fund_date', '>=', $request->from_date);
        if ($request->to_date) $query->where('fund_date', '<=', $request->to_date);
        if ($request->invoice_no) {
            $query->where(function($q) use ($request) {
                $q->where('invoice_no', 'like', "%{$request->invoice_no}%")
                  ->orWhere('id', $request->invoice_no);
            });
        }

        $perPage = $request->get('per_page', 10);
        $report_data = ($perPage === 'all') ? $query->orderBy('fund_date', 'desc')->get() : $query->orderBy('fund_date', 'desc')->paginate($perPage);
        return view('admin.reports.fund_transferred', compact('projects', 'report_data'));
    }

    public function projectExpense(Request $request)
    {
        $projects = Project::all();
        $query = Expense::with(['project', 'category']);

        if ($request->project_id) $query->where('project_id', $request->project_id);
        if ($request->from_date) $query->where('expense_date', '>=', $request->from_date);
        if ($request->to_date) $query->where('expense_date', '<=', $request->to_date);
        if ($request->invoice_no) {
            $query->where(function($q) use ($request) {
                $q->where('invoice_no', 'like', "%{$request->invoice_no}%")
                  ->orWhere('id', $request->invoice_no);
            });
        }

        $perPage = $request->get('per_page', 10);
        $report_data = ($perPage === 'all') ? $query->orderBy('expense_date', 'desc')->get() : $query->orderBy('expense_date', 'desc')->paginate($perPage);
        return view('admin.reports.project_expense', compact('projects', 'report_data'));
    }

    public function fundReturned(Request $request)
    {
        $projects = Project::all();
        $query = \App\Models\ManagerReturn::with(['project', 'employee']);

        if ($request->project_id) $query->where('project_id', $request->project_id);
        if ($request->from_date) $query->where('return_date', '>=', $request->from_date);
        if ($request->to_date) $query->where('return_date', '<=', $request->to_date);
        if ($request->invoice_no) {
            $query->where(function($q) use ($request) {
                $q->where('invoice_no', 'like', "%{$request->invoice_no}%")
                  ->orWhere('id', $request->invoice_no);
            });
        }

        $perPage = $request->get('per_page', 10);
        $report_data = ($perPage === 'all') ? $query->orderBy('return_date', 'desc')->get() : $query->orderBy('return_date', 'desc')->paginate($perPage);
        return view('admin.reports.fund_returned', compact('projects', 'report_data'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'report_type' => 'required|in:all,client_received,fund_pm,expense,fund_return',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
        ]);

        $projectId = $request->project_id;
        $type = $request->report_type;
        $from = $request->from_date;
        $to = $request->to_date;
        $invoiceNo = $request->invoice_no;

        $project = Project::findOrFail($projectId);
        $transactions = collect();

        // 1. Client Received
        if ($type === 'all' || $type === 'client_received') {
            $query = ClientPayment::where('project_id', $projectId);
            if ($from) $query->where('payment_date', '>=', $from);
            if ($to) $query->where('payment_date', '<=', $to);
            if ($invoiceNo) {
                $query->where(function($q) use ($invoiceNo) {
                    $q->where('invoice_no', 'like', "%{$invoiceNo}%")
                      ->orWhere('id', $invoiceNo);
                });
            }
            $payments = $query->get()->map(function($item) {
                return [
                    'date' => $item->payment_date,
                    'type' => 'Client Payment',
                    'method' => $item->payment_method,
                    'category' => '-',
                    'description' => $item->description ?? 'Client Payment',
                    'invoice_no' => $item->invoice_no ?? 'PAY-'.$item->id,
                    'invoice_url' => route('shared.payments.invoice', $item->id),
                    'credit' => $item->amount,
                    'debit' => 0,
                    'raw_date' => $item->payment_date->format('Y-m-d')
                ];
            });
            $transactions = $transactions->concat($payments);
        }

        // 2. Fund Transferred PM
        if ($type === 'all' || $type === 'fund_pm') {
            $query = ManagerFund::where('project_id', $projectId);
            if ($from) $query->where('fund_date', '>=', $from);
            if ($to) $query->where('fund_date', '<=', $to);
            if ($invoiceNo) {
                $query->where(function($q) use ($invoiceNo) {
                    $q->where('invoice_no', 'like', "%{$invoiceNo}%")
                      ->orWhere('id', $invoiceNo);
                });
            }
            $funds = $query->get()->map(function($item) {
                return [
                    'date' => $item->fund_date,
                    'type' => 'Fund Disbursed',
                    'method' => $item->payment_method,
                    'category' => '-',
                    'description' => $item->note ?? 'Fund Disbursed to Manager',
                    'invoice_no' => $item->invoice_no ?? 'FND-'.$item->id,
                    'invoice_url' => route('shared.funds.invoice', $item->id),
                    'credit' => 0,
                    'debit' => $item->amount,
                    'raw_date' => $item->fund_date->format('Y-m-d')
                ];
            });
            $transactions = $transactions->concat($funds);
        }

        // 3. Expenses
        if ($type === 'all' || $type === 'expense') {
            $query = Expense::with('category')->where('project_id', $projectId);
            if ($from) $query->where('expense_date', '>=', $from);
            if ($to) $query->where('expense_date', '<=', $to);
            if ($invoiceNo) {
                $query->where(function($q) use ($invoiceNo) {
                    $q->where('invoice_no', 'like', "%{$invoiceNo}%")
                      ->orWhere('id', $invoiceNo);
                });
            }
            $expenses = $query->get()->map(function($item) {
                return [
                    'date' => $item->expense_date,
                    'type' => 'Expense',
                    'method' => 'Cash', // Default for project expenses
                    'category' => $item->category->name ?? 'N/A',
                    'description' => $item->description ?? 'Project Expense',
                    'invoice_no' => $item->invoice_no ?? 'EXP-'.$item->id,
                    'invoice_url' => route('shared.expenses.invoice', $item->id),
                    'credit' => 0,
                    'debit' => $item->amount,
                    'raw_date' => $item->expense_date->format('Y-m-d')
                ];
            });
            $transactions = $transactions->concat($expenses);
        }

        // 4. Fund Returned
        if ($type === 'all' || $type === 'fund_return') {
            $query = \App\Models\ManagerReturn::where('project_id', $projectId);
            if ($from) $query->where('return_date', '>=', $from);
            if ($to) $query->where('return_date', '<=', $to);
            if ($invoiceNo) {
                $query->where(function($q) use ($invoiceNo) {
                    $q->where('invoice_no', 'like', "%{$invoiceNo}%")
                      ->orWhere('id', $invoiceNo);
                });
            }
            $returns = $query->get()->map(function($item) {
                return [
                    'date' => $item->return_date,
                    'type' => 'Fund Returned',
                    'method' => $item->payment_method,
                    'category' => '-',
                    'description' => $item->note ?? 'Fund Returned to Admin',
                    'invoice_no' => $item->invoice_no ?? 'RET-'.$item->id,
                    'invoice_url' => route('shared.returns.invoice', $item->id),
                    'credit' => $item->amount,
                    'debit' => 0,
                    'raw_date' => $item->return_date->format('Y-m-d')
                ];
            });
            $transactions = $transactions->concat($returns);
        }

        $report_data = $transactions->sortByDesc('date')->values();
        $selected_project = $project;
        $filters = $request->all();
        $projects = Project::all();

        return view('admin.reports.index', compact('projects', 'report_data', 'selected_project', 'filters'));
    }

    public function managerIndex(Request $request)
    {
        $projects = Project::where('employee_id', $request->user()->employee_id)->get();
        return view('manager.reports.index', compact('projects'));
    }

    public function managerGenerate(Request $request)
    {
        $data = $this->getManagerReportData($request);
        return view('manager.reports.index', array_merge($data, ['filters' => $request->all()]));
    }

    public function print(Request $request)
    {
        $data = $this->getAdminReportData($request);
        $totals = $this->calculateAdminTotals($data['report_data']);
        
        $type = $request->report_type;
        $titleText = "Project Financial Report";
        if($type == 'client_received') $titleText = "Client Received Report";
        if($type == 'fund_pm') $titleText = "Fund Transferred to PM Report";
        if($type == 'expense') $titleText = "Project Expenses Report";
        if($type == 'fund_return') $titleText = "Fund Returned Report";

        return view('admin.reports.print', array_merge($data, [
            'filters' => $request->all(), 
            'totals' => $totals,
            'titleText' => $titleText,
            'isManager' => false
        ]));
    }

    public function managerPrint(Request $request)
    {
        $data = $this->getManagerReportData($request);
        $totals = $this->calculateManagerTotals($data['report_data']);
        
        $type = $request->report_type;
        $titleText = "Project Financial Report";
        if($type == 'fund_pm') $titleText = "Fund Received Report";
        if($type == 'expense') $titleText = "Project Expenses Report";
        if($type == 'fund_return') $titleText = "Fund Returned Report";

        return view('admin.reports.print', array_merge($data, [
            'filters' => $request->all(), 
            'totals' => $totals,
            'titleText' => $titleText,
            'isManager' => true
        ]));
    }


    private function getAdminReportData(Request $request)
    {
        $projectId = $request->project_id;
        $type = $request->report_type;
        $from = $request->from_date;
        $to = $request->to_date;
        $invoiceNo = $request->invoice_no;

        $project = null;
        if($projectId) {
            $project = Project::find($projectId);
        }
        
        $transactions = collect();

        if ($type === 'all' || $type === 'client_received') {
            $query = ClientPayment::with('project');
            if ($projectId) $query->where('project_id', $projectId);
            if ($from) $query->where('payment_date', '>=', $from);
            if ($to) $query->where('payment_date', '<=', $to);
            if ($invoiceNo) {
                $query->where(function($q) use ($invoiceNo) {
                    $q->where('invoice_no', 'like', "%{$invoiceNo}%")
                      ->orWhere('id', $invoiceNo);
                });
            }
            $payments = $query->get()->map(function($item) {
                return [
                    'date' => $item->payment_date,
                    'type' => 'Client Payment',
                    'method' => $item->payment_method,
                    'category' => '-',
                    'description' => $item->description ?? 'Client Payment',
                    'invoice_no' => $item->invoice_no ?? 'PAY-'.$item->id,
                    'invoice_url' => route('shared.payments.invoice', $item->id),
                    'credit' => $item->amount,
                    'debit' => 0,
                    'project_id' => $item->project_id,
                    'project_name' => $item->project->project_name ?? 'N/A',
                ];
            });
            $transactions = $transactions->concat($payments);
        }

        if ($type === 'all' || $type === 'fund_pm') {
            $query = ManagerFund::with('project');
            if ($projectId) $query->where('project_id', $projectId);
            if ($from) $query->where('fund_date', '>=', $from);
            if ($to) $query->where('fund_date', '<=', $to);
            if ($invoiceNo) {
                $query->where(function($q) use ($invoiceNo) {
                    $q->where('invoice_no', 'like', "%{$invoiceNo}%")
                      ->orWhere('id', $invoiceNo);
                });
            }
            $funds = $query->get()->map(function($item) {
                return [
                    'date' => $item->fund_date,
                    'type' => 'Fund Disbursed',
                    'method' => $item->payment_method,
                    'category' => '-',
                    'description' => $item->note ?? 'Fund Disbursed to Manager',
                    'invoice_no' => $item->invoice_no ?? 'FND-'.$item->id,
                    'invoice_url' => route('shared.funds.invoice', $item->id),
                    'credit' => 0,
                    'debit' => $item->amount,
                    'project_id' => $item->project_id,
                    'project_name' => $item->project->project_name ?? 'N/A',
                ];
            });
            $transactions = $transactions->concat($funds);
        }

        if ($type === 'all' || $type === 'expense') {
            $query = Expense::with(['category', 'project']);
            if ($projectId) $query->where('project_id', $projectId);
            if ($from) $query->where('expense_date', '>=', $from);
            if ($to) $query->where('expense_date', '<=', $to);
            if ($invoiceNo) {
                $query->where(function($q) use ($invoiceNo) {
                    $q->where('invoice_no', 'like', "%{$invoiceNo}%")
                      ->orWhere('id', $invoiceNo);
                });
            }
            $expenses = $query->get()->map(function($item) {
                return [
                    'date' => $item->expense_date,
                    'type' => 'Expense',
                    'method' => 'Cash',
                    'category' => $item->category->name ?? 'N/A',
                    'description' => $item->description ?? 'Project Expense',
                    'invoice_no' => $item->invoice_no ?? 'EXP-'.$item->id,
                    'invoice_url' => route('shared.expenses.invoice', $item->id),
                    'credit' => 0,
                    'debit' => $item->amount,
                    'project_id' => $item->project_id,
                    'project_name' => $item->project->project_name ?? 'N/A',
                ];
            });
            $transactions = $transactions->concat($expenses);
        }

        if ($type === 'all' || $type === 'fund_return') {
            $query = \App\Models\ManagerReturn::with('project');
            if ($projectId) $query->where('project_id', $projectId);
            if ($from) $query->where('return_date', '>=', $from);
            if ($to) $query->where('return_date', '<=', $to);
            if ($invoiceNo) {
                $query->where(function($q) use ($invoiceNo) {
                    $q->where('invoice_no', 'like', "%{$invoiceNo}%")
                      ->orWhere('id', $invoiceNo);
                });
            }
            $returns = $query->get()->map(function($item) {
                return [
                    'date' => $item->return_date,
                    'type' => 'Fund Returned',
                    'method' => $item->payment_method,
                    'category' => '-',
                    'description' => $item->note ?? 'Fund Returned to Admin',
                    'invoice_no' => $item->invoice_no ?? 'RET-'.$item->id,
                    'invoice_url' => route('shared.returns.invoice', $item->id),
                    'credit' => $item->amount,
                    'debit' => 0,
                    'project_id' => $item->project_id,
                    'project_name' => $item->project->project_name ?? 'N/A',
                ];
            });
            $transactions = $transactions->concat($returns);
        }

        return [
            'project' => $project,
            'selected_project' => $project,
            'report_data' => $transactions->sortByDesc('date')->values(),
            'projects' => Project::all()
        ];
    }

    private function getManagerReportData(Request $request)
    {
        $employeeId = $request->user()->employee_id;
        $project = Project::where('id', $request->project_id)->where('employee_id', $employeeId)->firstOrFail();

        $type = $request->report_type;
        $from = $request->from_date;
        $to = $request->to_date;
        $invoiceNo = $request->invoice_no;

        $transactions = collect();

        if ($type === 'all' || $type === 'fund_pm') {
            $query = ManagerFund::where('project_id', $project->id)->where('employee_id', $employeeId);
            if ($from) $query->where('fund_date', '>=', $from);
            if ($to) $query->where('fund_date', '<=', $to);
            if ($invoiceNo) {
                $query->where(function($q) use ($invoiceNo) {
                    $q->where('invoice_no', 'like', "%{$invoiceNo}%")
                      ->orWhere('id', $invoiceNo);
                });
            }
            $funds = $query->get()->map(function($item) {
                return [
                    'date' => $item->fund_date,
                    'type' => 'Fund Received',
                    'method' => $item->payment_method,
                    'category' => '-',
                    'description' => $item->note ?? 'Fund Received from Admin',
                    'invoice_no' => $item->invoice_no ?? 'FND-'.$item->id,
                    'invoice_url' => route('shared.funds.invoice', $item->id),
                    'credit' => $item->amount,
                    'debit' => 0,
                ];
            });
            $transactions = $transactions->concat($funds);
        }

        if ($type === 'all' || $type === 'expense') {
            $query = Expense::with('category')->where('project_id', $project->id)->where('employee_id', $employeeId);
            if ($from) $query->where('expense_date', '>=', $from);
            if ($to) $query->where('expense_date', '<=', $to);
            if ($invoiceNo) {
                $query->where(function($q) use ($invoiceNo) {
                    $q->where('invoice_no', 'like', "%{$invoiceNo}%")
                      ->orWhere('id', $invoiceNo);
                });
            }
            $expenses = $query->get()->map(function($item) {
                return [
                    'date' => $item->expense_date,
                    'type' => 'Expense',
                    'method' => 'Cash',
                    'category' => $item->category->name,
                    'description' => $item->description ?? 'Project Expense',
                    'invoice_no' => $item->invoice_no ?? 'EXP-'.$item->id,
                    'invoice_url' => route('shared.expenses.invoice', $item->id),
                    'credit' => 0,
                    'debit' => $item->amount,
                ];
            });
            $transactions = $transactions->concat($expenses);
        }

        if ($type === 'all' || $type === 'fund_return') {
            $query = \App\Models\ManagerReturn::where('project_id', $project->id)->where('employee_id', $employeeId);
            if ($from) $query->where('return_date', '>=', $from);
            if ($to) $query->where('return_date', '<=', $to);
            if ($invoiceNo) {
                $query->where(function($q) use ($invoiceNo) {
                    $q->where('invoice_no', 'like', "%{$invoiceNo}%")
                      ->orWhere('id', $invoiceNo);
                });
            }
            $returns = $query->get()->map(function($item) {
                return [
                    'date' => $item->return_date,
                    'type' => 'Fund Returned',
                    'method' => $item->payment_method,
                    'category' => '-',
                    'description' => $item->note ?? 'Fund Returned to Admin',
                    'invoice_no' => $item->invoice_no ?? 'RET-'.$item->id,
                    'invoice_url' => route('shared.returns.invoice', $item->id),
                    'credit' => 0,
                    'debit' => $item->amount,
                ];
            });
            $transactions = $transactions->concat($returns);
        }

        return [
            'project' => $project,
            'selected_project' => $project,
            'report_data' => $transactions->sortByDesc('date')->values(),
            'projects' => Project::where('employee_id', $employeeId)->get()
        ];
    }

    private function calculateAdminTotals($report_data)
    {
        $client_received = $report_data->where('type', 'Client Payment')->sum('credit');
        $transferred_pm = $report_data->where('type', 'Fund Disbursed')->sum('debit');
        $pm_expenses = $report_data->where('type', 'Expense')->sum('debit');
        $fund_returned = $report_data->where('type', 'Fund Returned')->sum('credit');

        return [
            'client_received' => $client_received,
            'transferred_pm' => $transferred_pm,
            'pm_expenses' => $pm_expenses,
            'fund_returned' => $fund_returned,
            'office_balance' => ($client_received + $fund_returned) - $transferred_pm,
            'pm_hand_cash' => $transferred_pm - ($pm_expenses + $fund_returned)
        ];
    }

    private function calculateManagerTotals($report_data)
    {
        $fund_received = $report_data->where('type', 'Fund Received')->sum('credit');
        $expenses = $report_data->where('type', 'Expense')->sum('debit');
        $fund_returned = $report_data->where('type', 'Fund Returned')->sum('debit');

        return [
            'client_received' => 0,
            'transferred_pm' => $fund_received,
            'office_balance' => 0,
            'pm_hand_cash' => $fund_received - ($expenses + $fund_returned)
        ];
    }
}
