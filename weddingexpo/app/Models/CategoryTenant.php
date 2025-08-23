<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $expo_id
 * @property string $category
 * @property int $harga_jual
 * @property int $harga_modal
 * @property int $jumlah_unit
 * @property string|null $ukuran
 * @property string|null $deskripsi
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class CategoryTenant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'expo_id',
        'category',
        'harga_jual',
        'harga_modal',
        'jumlah_unit',
        'ukuran',
        'deskripsi',
        'status',
    ];

    /**
     * Relasi ke Expo
     */
    public function expo(): BelongsTo
    {
        return $this->belongsTo(Expo::class);
    }
}
