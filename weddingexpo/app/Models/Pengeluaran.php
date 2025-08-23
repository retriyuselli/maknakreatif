<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengeluaran extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'expo_id',
        'nama_pengeluaran',
        'keterangan',
        'nominal',
        'tanggal',
        'rekening_tujuan_id',
        'user_id',
        'bukti_transfer',
    ];

    public function expo(): BelongsTo
    {
        return $this->belongsTo(Expo::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rekeningTujuan(): BelongsTo
    {
        return $this->belongsTo(RekeningTujuan::class);
    }
}
