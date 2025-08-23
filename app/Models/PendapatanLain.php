<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PendapatanLain extends Model
{
    use HasFactory,
        SoftDeletes;

    protected $fillable = [
        'name',
        'payment_method_id',
        'nominal',
        'image',
        'tgl_bayar',
        'keterangan',
        'kategori_transaksi',
    ];

    protected $casts = [
        'tgl_bayar' => 'date',
        'nominal' => 'decimal:2',
        'kategori_transaksi' => 'string',
    ];

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
