<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'note',
        'date_expense',
        'amount',
        'vendor_id',
        'payment_method_id',
        'no_nd',
        'image',
        'kategori_transaksi',
    ];

    protected $casts = [
        'date_expense' => 'date', // Atau 'datetime' jika Anda menyimpan waktu juga
        'amount' => 'float', // Pastikan amount juga di-cast jika perlu
    ];

    public function category(): BelongsTo
    {
        // Assumes 'category_id' is the foreign key in the 'expenses' table
        // Assumes your Category model is App\Models\Category
        return $this->belongsTo(Category::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderProduct()
    {
        return $this->belongsTo(OrderProduct::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'payment_method_id');
    }
}
