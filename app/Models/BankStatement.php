<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;

class BankStatement extends Model
{
    protected $fillable = [
        'payment_method_id', // Changed to match migration and resource
        'period_start',
        'period_end',
        'file_path',
        'source_type',
        'status',
        'uploaded_at',

        'branch', // Cabang pembuka rekening
        'opening_balance', // Saldo awal rekening
        'closing_balance', // Saldo akhir rekening
        'no_of_debit', // Total number of debit transactions
        'tot_debit', // Total debit amount
        'no_of_credit', // Total number of credit transactions
        'tot_credit', // Total credit amount
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'uploaded_at' => 'datetime',
    ];

    public function paymentMethod(): BelongsTo // Corrected typo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public static function getStatusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'parsed' => 'Parsed',
            'failed' => 'Failed',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return Arr::get(self::getStatusOptions(), $this->status, $this->status);
    }

    public static function getSourceTypeOptions(): array
    {
        return [
            'pdf' => 'PDF',
            'excel' => 'Excel',
            'manual_input' => 'Manual Input',
        ];
    }
}
