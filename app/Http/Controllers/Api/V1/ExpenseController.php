<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        // Security: PM can only view their own project's expenses
        if ($request->user()->role === 'project_manager' && $project->employee_id !== $request->user()->employee_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $expenses = Expense::with(['category', 'employee'])->where('project_id', $projectId)->get();
        return response()->json($expenses);
    }

    public function store(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        // Only assigned PM can add expenses
        if ($request->user()->role === 'project_manager' && $project->employee_id !== $request->user()->employee_id) {
            return response()->json(['message' => 'Unauthorized. Only the assigned Project Manager can add expenses.'], 403);
        }

        $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date',
            'description' => 'nullable|string',
            'bill_image' => 'nullable|image|max:5120' // Max 5MB
        ]);

        $imagePath = null;
        if ($request->hasFile('bill_image')) {
            $imagePath = $request->file('bill_image')->store('receipts', 'public');
        }

        $expense = Expense::create([
            'project_id' => $project->id,
            'employee_id' => $project->employee_id, // Assigned PM
            'expense_category_id' => $request->expense_category_id,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'description' => $request->description,
            'bill_image' => $imagePath ? url('storage/' . $imagePath) : null,
        ]);

        return response()->json($expense->load(['category', 'employee']), 201);
    }

    public function show(Request $request, $id)
    {
        $expense = Expense::with(['category', 'employee', 'project'])->findOrFail($id);

        if ($request->user()->role === 'project_manager' && $expense->project->employee_id !== $request->user()->employee_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($expense);
    }
}
