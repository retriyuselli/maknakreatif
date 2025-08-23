<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $expo_id
 * @property int $vendor_id
 * @property \Illuminate\Support\Carbon $tanggal_booking
 * @property int $category_tenant_id
 * @property int $harga_jual
 * @property string $status_pembayaran
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Expo $expo
 * @property-read \App\Models\Vendor $vendor
 * @property-read \App\Models\CategoryTenant $categoryTenant
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DataPembayaran> $dataPembayarans
 * @property-read int|null $data_pembayarans_count
 * @property-read int $tot_nominal
 */
class Partisipasi extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'expo_id',
        'vendor_id',
        'tanggal_booking',
        'category_tenant_id',
        'harga_jual',
        'status_pembayaran',
        'blok_tenant',
        'vendor_pendamping',
    ];

    protected $casts = [
        'tanggal_booking' => 'date',
        'harga_jual' => 'integer',
    ];

    public function expo(): BelongsTo
    {
        return $this->belongsTo(Expo::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function dataPembayarans(): HasMany
    {
        return $this->hasMany(DataPembayaran::class);
    }

    public function categoryTenant(): BelongsTo
    {
        return $this->belongsTo(CategoryTenant::class);
    }

    protected function totNominal(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->dataPembayarans()->sum('nominal'),
        );
    }
}
