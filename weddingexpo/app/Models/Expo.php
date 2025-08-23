<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $nama_expo
 * @property string $tanggal_mulai
 * @property string $tanggal_selesai
 * @property string $lokasi
 * @property string $tahun
 * @property string $periode
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Partisipasi> $partisipasis
 * @property-read int|null $partisipasis_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\JenisTenant> $jenisTenants
 * @property-read int|null $jenis_tenants_count
 */
class Expo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nama_expo',
        'tanggal_mulai',
        'tanggal_selesai',
        'lokasi',
        'status',
        'periode',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'status' => 'boolean',
    ];

    /**
     * Relasi ke partisipasi expo
     */
    public function partisipasis(): HasMany
    {
        return $this->hasMany(Partisipasi::class);
    }

    /**
     * Relasi ke kategori tenant
     */
    public function categoryTenants(): HasMany
    {
        return $this->hasMany(CategoryTenant::class);
    }
}
