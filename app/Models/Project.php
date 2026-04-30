<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected static function booted()
    {
        static::addGlobalScope('manager_isolation', function ($builder) {
            if (auth()->check() && auth()->user()->role === 'project_manager') {
                $employeeId = auth()->user()->employee_id;
                if ($employeeId) {
                    $builder->where('projects.employee_id', $employeeId);
                } else {
                    $builder->whereRaw('1=0');
                }
            }
        });
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function clientPayments(): HasMany
    {
        return $this->hasMany(ClientPayment::class);
    }

    public function managerFunds(): HasMany
    {
        return $this->hasMany(ManagerFund::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function managerReturns(): HasMany
    {
        return $this->hasMany(ManagerReturn::class);
    }
}
