<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class User extends Authenticatable implements HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status_id',
        'avatar_url',
        'expire_date',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => 'array',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'expire_date' => 'datetime',
        ];
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url ? Storage::url("$this->avatar_url") : null;
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

        public function firstEmployee(): HasOne
    {
        return $this->hasOne(Employee::class)->oldestOfMany();
    }

    public function getClosingAttribute()
    {
        return $this->orders->sum('total_price');
    }
    
    public function getAmCountAttribute()
    {
        $totAM = Order::where('user_id', $this->id)->count();
        return $totAM;
    }
    
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true; 
    }

    /**
     * Check if user account is expired
     */
    public function isExpired(): bool
    {
        if (!$this->expire_date) {
            return false;
        }
        
        return Carbon::now()->greaterThan($this->expire_date);
    }

    /**
     * Check if user account will expire soon (within 7 days)
     */
    public function isExpiringSoon(): bool
    {
        if (!$this->expire_date) {
            return false;
        }
        
        $expireDate = Carbon::parse($this->expire_date);
        $sevenDaysFromNow = Carbon::now()->addDays(7);
        
        return Carbon::now()->lessThan($expireDate) && $expireDate->lessThanOrEqualTo($sevenDaysFromNow);
    }

    /**
     * Get days until expiration
     */
    public function getDaysUntilExpiration(): ?int
    {
        if (!$this->expire_date) {
            return null;
        }
        
        return (int) Carbon::now()->diffInDays($this->expire_date, false);
    }
}
