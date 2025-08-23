<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class DataPribadi extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama_lengkap',
        'email',
        'nomor_telepon',
        'tanggal_lahir',
        'tanggal_mulai_gabung',
        'jenis_kelamin',
        'alamat',
        'foto',
        'pekerjaan', 
        'gaji',
        'motivasi_kerja',
        'pelatihan',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_mulai_gabung' => 'date',
        'gaji' => 'decimal:2', // Menambahkan cast untuk gaji
    ];

    /**
     * Get the URL for the profile photo.
     *
     * @return string|null
     */
    public function getFotoUrlAttribute(): ?string
    {
        if ($this->foto) {
            return Storage::url($this->foto);
        }
        // Anda bisa mengembalikan URL default jika tidak ada foto
        // return "https://ui-avatars.com/api/?name=" . urlencode($this->nama_lengkap) . "&color=FFFFFF&background=0D83DD";
        return null;
    }

    /**
     * Get the age of the person.
     *
     * @return int|null
     */
    public function getUsiaAttribute(): ?int
    {
        if ($this->tanggal_lahir) {
            return Carbon::parse($this->tanggal_lahir)->age;
        }
        return null;
    }

    /**
     * Get the formatted salary.
     *
     * @return string
     */
    public function getFormattedGajiAttribute(): string
    {
        return 'Rp ' . number_format($this->gaji ?: 0, 0, ',', '.');
    }

    /**
     * Set the nomor telepon, removing common prefixes.
     *
     * @param  string|null  $value
     * @return void
     */
    public function setNomorTeleponAttribute(?string $value): void
    {
        $this->attributes['nomor_telepon'] = preg_replace('/^(\+62|0)/', '', $value);
    }

    public function getInitialsAttribute(): string
    {
        $name = trim($this->nama_lengkap ?? '');
        if (empty($name)) {
            return 'N/A';
        }
        $nameParts = preg_split('/\s+/', $name);
        $initials = strtoupper(substr($nameParts[0], 0, 1));
        if (count($nameParts) > 1) {
            $initials .= strtoupper(substr(end($nameParts), 0, 1));
        }
        return $initials;
    }
}
