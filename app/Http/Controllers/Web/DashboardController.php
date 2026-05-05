<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\ProjectFinancialService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $financialService;

    public function __construct(ProjectFinancialService $financialService)
    {
        $this->financialService = $financialService;
    }

    public function adminDashboard(Request $request)
    {
        $query = Project::query();

        if ($request->filled('project_id')) {
            $query->where('id', $request->project_id);
        }
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $projects = $query->orderBy('created_at', 'desc')->get();
        $totalProjects = $projects->count();
        $activeProjects = $projects->whereIn('status', ['running', 'pending'])->count();
        
        $summaries = [];
        $totalProfitLoss = 0;
        $totalPmHandCash = 0;
        
        $searchTotals = [
            'budget' => 0,
            'payments' => 0,
            'funds' => 0,
            'returns' => 0,
            'expenses' => 0,
            'admin_expenses' => 0,
            'balance' => 0,
            'profit_loss' => 0
        ];

        foreach ($projects as $project) {
            $summary = $this->financialService->getProjectSummary(
                $project, 
                $request->start_date, 
                $request->end_date
            );
            $summaries[] = $summary;
            
            $searchTotals['budget'] += $summary['estimated_budget'];
            $searchTotals['payments'] += $summary['total_client_payments'];
            $searchTotals['funds'] += $summary['total_manager_funds'];
            $searchTotals['returns'] += $summary['total_manager_returns'];
            $searchTotals['expenses'] += $summary['total_expenses'];
            $searchTotals['admin_expenses'] += $summary['admin_expenses'];
            $searchTotals['balance'] += $summary['manager_cash_balance'];
            $searchTotals['profit_loss'] += $summary['profit_loss'];
            
            $totalProfitLoss += $summary['profit_loss'];
            $totalPmHandCash += $summary['manager_cash_balance'];
        }

        $allProjects = Project::orderBy('project_name')->get();
        $employees = \App\Models\Employee::whereHas('user', function($q) {
            $q->where('role', 'project_manager');
        })->get();

        $isSearch = $request->project_id || $request->employee_id || $request->start_date || $request->end_date;
        $selectedManager = null;
        if ($request->employee_id) {
            $selectedManager = \App\Models\Employee::find($request->employee_id);
        } elseif ($request->project_id) {
            $proj = Project::find($request->project_id);
            $selectedManager = $proj ? $proj->manager : null;
        }
        
        return view('admin.dashboard', compact(
            'totalProjects', 
            'activeProjects', 
            'totalProfitLoss', 
            'totalPmHandCash', 
            'summaries',
            'allProjects',
            'employees',
            'searchTotals',
            'isSearch',
            'selectedManager'
        ));
    }

    public function managerDashboard(Request $request)
    {
        $employeeId = $request->user()->employee_id;
        
        if (!$employeeId) {
            return view('manager.dashboard', [
                'projects' => collect(),
                'allMyProjects' => collect(),
                'totalReceived' => 0,
                'totalExpenses' => 0,
                'totalReturns' => 0,
                'balance' => 0,
                'assignedProjectsCount' => 0,
                'error' => 'No employee record linked to your user account.'
            ]);
        }

        $query = Project::where('employee_id', $employeeId);

        if ($request->filled('project_id')) {
            $query->where('id', $request->project_id);
        }

        $allMyProjects = Project::where('employee_id', $employeeId)->orderBy('project_name', 'asc')->get();
        $projects = $query->orderBy('created_at', 'desc')->get();
        $assignedProjectsCount = $projects->count();
        
        $totalReceived = 0;
        $totalExpenses = 0;
        $totalReturns = 0;
        $balance = 0;

        foreach ($projects as $project) {
            $summary = $this->financialService->getProjectSummary($project, null, null, true);
            $project->summary = $summary;
            $totalReceived += $summary['total_manager_funds'];
            $totalExpenses += $summary['total_expenses'];
            $totalReturns += $summary['total_manager_returns'];
            $balance += $summary['manager_cash_balance'];
        }

        return view('manager.dashboard', compact('projects', 'allMyProjects', 'assignedProjectsCount', 'totalReceived', 'totalExpenses', 'totalReturns', 'balance'));
    }
}
