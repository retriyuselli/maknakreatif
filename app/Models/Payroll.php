<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payroll extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'period_month',
        'period_year',
        'monthly_salary',
        'annual_salary',
        'bonus',
        'last_review_date',
        'next_review_date',
        'notes',
    ];

    protected $casts = [
        'period_month' => 'integer',
        'period_year' => 'integer',
        'monthly_salary' => 'decimal:2',
        'annual_salary' => 'decimal:2',
        'bonus' => 'decimal:2',
        'last_review_date' => 'date',
        'next_review_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($payroll) {
            // Set default period jika belum diisi
            if (!$payroll->period_month) {
                $payroll->period_month = now()->month;
            }
            if (!$payroll->period_year) {
                $payroll->period_year = now()->year;
            }
            
            // Otomatis hitung annual_salary setiap kali monthly_salary berubah
            if ($payroll->monthly_salary) {
                $payroll->annual_salary = $payroll->monthly_salary * 12;
            }
        });
    }

    // Accessor untuk menghitung annual salary berdasarkan monthly salary
    public function getCalculatedAnnualSalaryAttribute(): float
    {
        return (float) ($this->monthly_salary ?? 0) * 12;
    }

    // Accessor untuk mendapatkan total kompensasi
    public function getTotalCompensationAttribute(): float
    {
        return $this->calculated_annual_salary + (float) ($this->bonus ?? 0);
    }

    // Accessor untuk periode yang mudah dibaca
    public function getPeriodNameAttribute(): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        $monthName = $months[$this->period_month] ?? 'Unknown';
        return "{$monthName} {$this->period_year}";
    }

    // Accessor untuk format currency dengan titik
    public function getFormattedMonthlySalaryAttribute(): string
    {
        return number_format((float) $this->monthly_salary, 0, '.', '.');
    }

    public function getFormattedAnnualSalaryAttribute(): string
    {
        return number_format($this->calculated_annual_salary, 0, '.', '.');
    }

    public function getFormattedBonusAttribute(): string
    {
        return number_format((float) ($this->bonus ?? 0), 0, '.', '.');
    }

    public function getFormattedTotalCompensationAttribute(): string
    {
        return number_format($this->total_compensation, 0, '.', '.');
    }

    // Accessor untuk format currency dengan Rp prefix
    public function getFormattedMonthlySalaryWithPrefixAttribute(): string
    {
        return 'Rp ' . $this->formatted_monthly_salary;
    }

    public function getFormattedAnnualSalaryWithPrefixAttribute(): string
    {
        return 'Rp ' . $this->formatted_annual_salary;
    }

    public function getFormattedCalculatedAnnualSalaryWithPrefixAttribute(): string
    {
        return 'Rp ' . number_format($this->calculated_annual_salary, 0, '.', '.');
    }

    public function getFormattedBonusWithPrefixAttribute(): string
    {
        return 'Rp ' . $this->formatted_bonus;
    }

    public function getFormattedTotalCompensationWithPrefixAttribute(): string
    {
        return 'Rp ' . $this->formatted_total_compensation;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
