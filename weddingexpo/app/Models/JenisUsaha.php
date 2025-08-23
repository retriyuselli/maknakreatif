<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $nama_jenis_usaha
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class JenisUsaha extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nama_jenis_usaha',
    ];

    /**
     * Relasi ke vendor
     */
    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class, 'jenis_usaha_id');
    }
}
