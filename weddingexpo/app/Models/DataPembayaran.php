<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\DataPembayaran
 *
 * @property int $id
 * @property int $partisipasi_id
 * @property string $nama_pembayar
 * @property int $nominal
 * @property string $tanggal_bayar
 * @property string $metode_pembayaran
 * @property string $bukti_transfer
 * @property int $rekening_tujuan_id
 * @property string|null $keterangan
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @property-read \App\Models\Partisipasi $partisipasi
 * @property-read \App\Models\RekeningTujuan $rekeningTujuan
 */
class DataPembayaran extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'partisipasi_id',
        'nama_pembayar',
        'nominal',
        'tanggal_bayar',
        'metode_pembayaran',
        'bukti_transfer',
        'rekening_tujuan_id',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_bayar' => 'date',
    ];

    /**
     * Get the partisipasi that owns the data pembayaran.
     */
    public function partisipasi(): BelongsTo
    {
        return $this->belongsTo(Partisipasi::class);
    }

    /**
     * Get the rekening tujuan for the data pembayaran.
     */
    public function rekeningTujuan(): BelongsTo
    {
        return $this->belongsTo(RekeningTujuan::class);
    }

}
