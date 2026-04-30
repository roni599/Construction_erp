<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function index()
    {
        return response()->json(Employee::with('user')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:employees,email|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'nid' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'join_date' => 'nullable|date',
            'create_user' => 'boolean',
            'password' => 'required_if:create_user,true|string|min:6'
        ]);

        DB::beginTransaction();
        try {
            $employee = Employee::create($request->only([
                'name', 'email', 'phone', 'nid', 'address', 'join_date'
            ]));

            if ($request->create_user) {
                User::create([
                    'name' => $employee->name,
                    'email' => $employee->email,
                    'password' => Hash::make($request->password),
                    'role' => 'project_manager',
                    'employee_id' => $employee->id,
                ]);
            }

            DB::commit();
            return response()->json($employee->load('user'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating employee', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $employee = Employee::with('user')->findOrFail($id);
        return response()->json($employee);
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email|unique:employees,email,'.$employee->id,
            'phone' => 'nullable|string|max:50',
            'nid' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'join_date' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        $employee->update($request->all());

        if ($request->has('email') && $employee->user) {
            $employee->user->update(['email' => $request->email]);
        }

        return response()->json($employee->load('user'));
    }
}
