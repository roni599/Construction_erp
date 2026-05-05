<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryWebController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search');
        
        $query = ExpenseCategory::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if (auth()->user()->role === 'project_manager') {
            $employeeId = auth()->user()->employee_id;
            $query->withCount(['expenses' => function($q) use ($employeeId) {
                $q->where('employee_id', $employeeId);
            }]);
        } else {
            $query->withCount('expenses');
        }

        $categories = ($perPage === 'all') 
            ? $query->orderBy('created_at', 'desc')->get() 
            : $query->orderBy('created_at', 'desc')->paginate($perPage);

        return view('shared.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name',
            'description' => 'nullable|string'
        ]);

        ExpenseCategory::create($request->all());
        return back()->with('success', 'Category created successfully.');
    }

    public function update(Request $request, $id)
    {
        $category = ExpenseCategory::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name,' . $id,
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $category->update($request->all());
        return back()->with('success', 'Category updated successfully.');
    }

    public function destroy($id)
    {
        $category = ExpenseCategory::withCount('expenses')->findOrFail($id);

        if ($category->expenses_count > 0) {
            return back()->with('error', 'Cannot delete category. It has associated expenses.');
        }

        $category->delete();
        return back()->with('success', 'Category deleted successfully.');
    }
}
