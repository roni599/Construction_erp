<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagerReturn extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'return_date' => 'date',
    ];

    protected static function booted()
    {
        static::addGlobalScope('manager_isolation', function ($builder) {
            if (auth()->check() && auth()->user()->role === 'project_manager') {
                $employeeId = auth()->user()->employee_id;
                if ($employeeId) {
                    $builder->where('manager_returns.employee_id', $employeeId);
                } else {
                    $builder->whereRaw('1=0');
                }
            }
        });
        
        static::created(function ($return) {
            if (!$return->invoice_no) {
                $year = $return->return_date ? $return->return_date->format('Y') : now()->format('Y');
                $return->invoice_no = "IN-ret{$year}{$return->id}";
                $return->saveQuietly();
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

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
