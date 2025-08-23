<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PengeluaranLain extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'amount',
        'payment_method_id',
        'date_expense',
        'image',
        'no_nd',
        'note',
        'kategori_transaksi',
    ];

    protected $casts = [
        'date_expense' => 'date',
        'amount' => 'decimal:2',
        'kategori_transaksi' => 'string',
    ];

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2);
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->date_expense->format('d F Y');
    }

    public function toArray()
    {
        $attributes = parent::toArray();
        $attributes['formatted_amount'] = $this->formatted_amount;
        $attributes['formatted_date'] = $this->formatted_date;
        return $attributes;
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function getPaymentMethodNameAttribute(): string
    {
        return $this->paymentMethod ? $this->paymentMethod->name : 'N/A';
    }

    public function getFormattedDateExpenseAttribute(): string
    {
        return $this->date_expense ? $this->date_expense->format('d F Y') : 'N/A';
    }

    public function getKategoriTransaksiLabelAttribute(): string
    {
        return $this->kategori_transaksi === 'uang_masuk' ? 'Income' : 'Expense';
    }
}
