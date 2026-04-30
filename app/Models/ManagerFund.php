<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagerFund extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'fund_date' => 'date',
    ];

    protected static function booted()
    {
        static::addGlobalScope('manager_isolation', function ($builder) {
            if (auth()->check() && auth()->user()->role === 'project_manager') {
                $employeeId = auth()->user()->employee_id;
                if ($employeeId) {
                    $builder->where('manager_funds.employee_id', $employeeId);
                } else {
                    $builder->whereRaw('1=0');
                }
            }
        });

        static::created(function ($fund) {
            if (!$fund->invoice_no) {
                $year = $fund->fund_date ? $fund->fund_date->format('Y') : now()->format('Y');
                $fund->invoice_no = "IN-fund{$year}{$fund->id}";
                $fund->saveQuietly();
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

    public function givenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'given_by');
    }
}
