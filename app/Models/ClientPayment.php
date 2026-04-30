<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientPayment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'payment_date' => 'date',
    ];

    protected static function booted()
    {
        static::created(function ($payment) {
            if (!$payment->invoice_no) {
                $year = $payment->payment_date ? $payment->payment_date->format('Y') : now()->format('Y');
                $payment->invoice_no = "IN-pay{$year}{$payment->id}";
                $payment->saveQuietly();
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
