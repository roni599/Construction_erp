<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::with('manager');
        
        // If PM is logged in, show only their projects
        if ($request->user()->role === 'project_manager') {
            $query->where('employee_id', $request->user()->employee_id);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_name' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'client_phone' => 'nullable|string|max:50',
            'client_email' => 'nullable|email|max:255',
            'location' => 'nullable|string',
            'estimated_budget' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'employee_id' => 'required|exists:employees,id',
            'description' => 'nullable|string'
        ]);

        $project = Project::create($request->all());

        return response()->json($project->load('manager'), 201);
    }

    public function show(Request $request, $id)
    {
        $project = Project::with('manager')->findOrFail($id);

        if ($request->user()->role === 'project_manager' && $project->employee_id !== $request->user()->employee_id) {
            return response()->json(['message' => 'Unauthorized access to project.'], 403);
        }

        return response()->json($project);
    }

    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        
        $request->validate([
            'project_name' => 'sometimes|required|string|max:255',
            'client_name' => 'sometimes|required|string|max:255',
            'client_phone' => 'nullable|string|max:50',
            'client_email' => 'nullable|email|max:255',
            'location' => 'nullable|string',
            'estimated_budget' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'status' => 'sometimes|required|in:pending,running,completed,hold',
            'employee_id' => 'sometimes|required|exists:employees,id',
            'description' => 'nullable|string'
        ]);

        $project->update($request->all());

        return response()->json($project->load('manager'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,running,completed,hold'
        ]);

        $project = Project::findOrFail($id);
        $project->update(['status' => $request->status]);

        return response()->json($project);
    }
}
