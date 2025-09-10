<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'status',
        'avatar_url',
        'expire_date',
        'role',
        'status_user',

        // Personal information fields
        'phone_number',
        'address',
        'date_of_birth',
        'gender',
        'hire_date',
        'last_working_date',
        'department',
        'annual_leave_quota'
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
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'avatar'
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
            'date_of_birth' => 'date',
            'hire_date' => 'date',
            'last_working_date' => 'date',
        ];
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if ($this->avatar_url) {
            return Storage::url($this->avatar_url);
        }
        
        return null;
    }

    /**
     * Get the avatar URL for frontend display
     */
    public function getAvatarAttribute(): ?string
    {
        if ($this->avatar_url) {
            return Storage::url($this->avatar_url);
        }
        
        return null;
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
    
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function firstEmployee(): HasOne
    {
        return $this->hasOne(Employee::class)->oldestOfMany();
    }

    public function latestEmployee(): HasOne
    {
        return $this->hasOne(Employee::class)->latestOfMany();
    }

    public function activeEmployee(): HasOne
    {
        return $this->hasOne(Employee::class)->whereNull('date_of_out');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class, 'created_by');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(\Illuminate\Notifications\DatabaseNotification::class, 'notifiable_id');
    }

    // Computed attributes for HR data
    public function getClosingAttribute()
    {
        return $this->orders->sum('total_price');
    }
    
    public function getAmCountAttribute()
    {
        $totAM = Order::where('user_id', $this->id)->count();
        return $totAM;
    }

    public function getTotalRevenueAttribute()
    {
        return $this->orders()->where('is_paid', true)->sum('total_price');
    }

    public function getPendingOrdersCountAttribute()
    {
        return $this->orders()->where('status', \App\Enums\OrderStatus::Pending)->count();
    }

    public function getCompletedOrdersCountAttribute()
    {
        return $this->orders()->where('status', \App\Enums\OrderStatus::Done)->count();
    }

    public function getProcessingOrdersCountAttribute()
    {
        return $this->orders()->where('status', \App\Enums\OrderStatus::Processing)->count();
    }

    public function getCancelledOrdersCountAttribute()
    {
        return $this->orders()->where('status', \App\Enums\OrderStatus::Cancelled)->count();
    }

    public function getAverageOrderValueAttribute()
    {
        $ordersCount = $this->orders()->count();
        if ($ordersCount === 0) return 0;
        
        return $this->orders()->sum('total_price') / $ordersCount;
    }

    public function getMonthlyRevenueAttribute()
    {
        return $this->orders()
            ->where('is_paid', true)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_price');
    }

    public function getYearlyRevenueAttribute()
    {
        return $this->orders()
            ->where('is_paid', true)
            ->whereYear('created_at', now()->year)
            ->sum('total_price');
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

    // new fields
    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    /**
     * Get the employee ID attribute
     * Format: EMP-0001, EMP-0002, etc.
     */
    public function getEmployeeIdAttribute()
    {
        return 'EMP-' . str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }
}
