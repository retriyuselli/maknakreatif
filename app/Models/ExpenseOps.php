<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseOps extends Model
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

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date_expense' => 'date',
        'amount' => 'decimal:2',
    ];

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2);
    }

    /**
     * Get the formatted date
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->date_expense->format('d F Y');
    }

    /**
     * Custom attributes for export/display
     */
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

    // Query scope for filtering by amount range
    public function scopeAmountRange($query, $range)
    {
        return match ($range) {
            'low' => $query->where('amount', '<', 1000000),
            'medium' => $query->whereBetween('amount', [1000000, 5000000]),
            'high' => $query->where('amount', '>', 5000000),
            default => $query,
        };
    }

    // Query scope for filtering by date range
    public function scopeDateRange($query, $dateFrom, $dateUntil)
    {
        return 
            $query
                ->when($dateFrom, fn($q) => $q->whereDate('date_expense', '>=', $dateFrom))
                ->when($dateUntil, fn($q) => $q->whereDate('date_expense', '<=', $dateUntil));
    }
}
