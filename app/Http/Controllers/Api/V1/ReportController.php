<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\ProjectFinancialService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $financialService;

    public function __construct(ProjectFinancialService $financialService)
    {
        $this->financialService = $financialService;
    }

    public function dashboard(Request $request)
    {
        // Admin gets overall stats
        $projects = Project::all();
        $totalProjects = $projects->count();
        $activeProjects = $projects->whereIn('status', ['running', 'pending'])->count();
        
        $summaries = [];
        $totalProfitLoss = 0;

        foreach ($projects as $project) {
            $summary = $this->financialService->getProjectSummary($project);
            $summaries[] = $summary;
            $totalProfitLoss += $summary['profit_loss'];
        }

        return response()->json([
            'total_projects' => $totalProjects,
            'active_projects' => $activeProjects,
            'total_profit_loss' => $totalProfitLoss,
            'projects_summary' => $summaries
        ]);
    }

    public function projectReport(Request $request, $id)
    {
        $project = Project::with('manager')->findOrFail($id);

        if ($request->user()->role === 'project_manager' && $project->employee_id !== $request->user()->employee_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $summary = $this->financialService->getProjectSummary($project);

        return response()->json([
            'project' => $project,
            'financial_summary' => $summary
        ]);
    }

    public function projectLedger(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        if ($request->user()->role === 'project_manager' && $project->employee_id !== $request->user()->employee_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $ledger = $this->financialService->getProjectLedger($project);

        return response()->json([
            'project_name' => $project->project_name,
            'ledger' => $ledger
        ]);
    }
}
