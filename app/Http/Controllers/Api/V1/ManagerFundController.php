<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ManagerFund;
use App\Models\Project;
use Illuminate\Http\Request;

class ManagerFundController extends Controller
{
    public function index($projectId)
    {
        $funds = ManagerFund::with(['givenBy', 'employee'])->where('project_id', $projectId)->get();
        return response()->json($funds);
    }

    public function store(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'fund_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_banking',
            'note' => 'nullable|string',
        ]);

        $fund = ManagerFund::create([
            'project_id' => $project->id,
            'employee_id' => $project->employee_id, // Goes to the assigned manager
            'amount' => $request->amount,
            'fund_date' => $request->fund_date,
            'payment_method' => $request->payment_method,
            'note' => $request->note,
            'given_by' => $request->user()->id,
        ]);

        return response()->json($fund->load(['givenBy', 'employee']), 201);
    }
}
