<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'expense_date' => 'date',
    ];

    protected static function booted()
    {
        static::addGlobalScope('manager_isolation', function ($builder) {
            if (auth()->check() && auth()->user()->role === 'project_manager') {
                $employeeId = auth()->user()->employee_id;
                if ($employeeId) {
                    $builder->where('expenses.employee_id', $employeeId);
                } else {
                    $builder->whereRaw('1=0');
                }
            }
        });

        static::created(function ($expense) {
            if (!$expense->invoice_no) {
                $year = $expense->expense_date ? $expense->expense_date->format('Y') : now()->format('Y');
                $expense->invoice_no = "IN-exp{$year}{$expense->id}";
                $expense->saveQuietly();
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function getBillImageAttribute($value)
    {
        if (!$value) return null;
        if (str_starts_with($value, 'http')) return $value;
        return asset('storage/' . $value);
    }
}
