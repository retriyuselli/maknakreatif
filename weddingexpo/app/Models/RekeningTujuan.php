<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $nama_bank
 * @property string $nomor_rekening
 * @property string $nama_pemilik
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class RekeningTujuan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nama_bank',
        'nomor_rekening',
        'nama_pemilik',
    ];

    /**
     * Relasi ke data pembayaran
     */
    public function dataPembayarans(): HasMany
    {
        return $this->hasMany(DataPembayaran::class, 'rekening_tujuan_id');
    }

    /**
     * Relasi ke pengeluaran
     */
    public function pengeluarans(): HasMany
    {
        return $this->hasMany(Pengeluaran::class, 'rekening_tujuan_id');
    }

    /**
     * Relasi ke pengeluaran lain
     */
    public function pengeluaranLains(): HasMany
    {
        return $this->hasMany(PengeluaranLain::class, 'rekening_tujuan_id');
    }
}
