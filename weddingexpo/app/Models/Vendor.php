<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $nama_vendor
 * @property int $jenis_usaha_id
 * @property string $alamat
 * @property string $kota
 * @property string $no_telepon
 * @property string $email
 * @property string $nama_pic
 * @property string $no_wa_pic
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Vendor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nama_vendor',
        'jenis_usaha_id',
        'alamat',
        'kota',
        'no_telepon',
        'email',
        'nama_pic',
        'no_wa_pic',
    ];

    /**
     * Relasi ke jenis usaha
     */
    public function jenisUsaha(): BelongsTo
    {
        return $this->belongsTo(JenisUsaha::class, 'jenis_usaha_id');
    }

    /**
     * Relasi ke partisipasi
     */
    public function partisipasis(): HasMany
    {
        return $this->hasMany(Partisipasi::class);
    }
}
