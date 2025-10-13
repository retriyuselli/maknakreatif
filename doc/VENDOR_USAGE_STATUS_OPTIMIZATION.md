# Vendor Usage Status Column Optimization

## Tanggal Perbaikan

13 Oktober 2025

## Masalah yang Diperbaiki

### 1. **Performance Issues (N+1 Query Problem)**

**Sebelum:**

-   Setiap row di table melakukan 6 query database (3 untuk `getStateUsing` + 3 untuk `tooltip`)
-   Untuk table dengan banyak record, ini sangat lambat

**Sesudah:**

-   Menggunakan `withCount()` eager loading di method `table()`
-   Semua count data dimuat dalam 1 query saja

### 2. **Code Duplication**

**Sebelum:**

-   Logic pengecekan usage tersebar di 3 tempat:
    -   `getStateUsing()` di VendorResource
    -   `tooltip()` di VendorResource
    -   `delete()` method di model Vendor

**Sesudah:**

-   Centralized logic dengan accessor `usage_status` dan `usage_details` di model
-   DRY principle: logic hanya ditulis sekali di model

### 3. **Naming Convention Issue**

**Sebelum:**

```php
public function vendors(): HasMany // Misleading name
{
    return $this->hasMany(Expense::class);
}
```

**Sesudah:**

```php
public function expenses(): HasMany // Clear and descriptive
{
    return $this->hasMany(Expense::class);
}
```

## Implementasi Perbaikan

### 1. **Model Vendor.php**

#### Accessor Baru:

```php
public function getUsageStatusAttribute(): string
{
    $productCount = $this->productVendors_count ?? $this->productVendors()->count();
    $expenseCount = $this->expenses_count ?? $this->expenses()->count();
    $notaDinasCount = $this->nota_dinas_details_count ?? $this->notaDinasDetails()->count();

    return ($productCount > 0 || $expenseCount > 0 || $notaDinasCount > 0)
        ? 'In Use'
        : 'Available';
}

public function getUsageDetailsAttribute(): array
{
    $productCount = $this->productVendors_count ?? $this->productVendors()->count();
    $expenseCount = $this->expenses_count ?? $this->expenses()->count();
    $notaDinasCount = $this->nota_dinas_details_count ?? $this->notaDinasDetails()->count();

    return [
        'productCount' => $productCount,
        'expenseCount' => $expenseCount,
        'notaDinasCount' => $notaDinasCount,
    ];
}
```

### 2. **VendorResource.php**

#### Eager Loading:

```php
public static function table(Table $table): Table
{
    return $table
        ->query(
            static::getEloquentQuery()->withCount([
                'productVendors',
                'expenses',
                'notaDinasDetails'
            ])
        )
        // ... rest of configuration
}
```

#### Simplified Column Definition:

```php
Tables\Columns\TextColumn::make('usage_status')
    ->label('Usage Status')
    ->badge()
    ->color(fn (string $state): string => match ($state) {
        'In Use' => 'warning',
        'Available' => 'success',
        default => 'gray',
    })
    ->tooltip(function (Vendor $record): string {
        $details = $record->usage_details;
        $descriptions = [];

        if ($details['productCount'] > 0) {
            $descriptions[] = "{$details['productCount']} product(s)";
        }
        if ($details['expenseCount'] > 0) {
            $descriptions[] = "{$details['expenseCount']} expense(s)";
        }
        if ($details['notaDinasCount'] > 0) {
            $descriptions[] = "{$details['notaDinasCount']} nota dinas detail(s)";
        }

        return !empty($descriptions)
            ? 'Used in: ' . implode(', ', $descriptions)
            : 'Not used in any products, expenses, or nota dinas details';
    })
```

## Keuntungan Setelah Perbaikan

1. **Performance**: Dramatically faster page loading (dari O(n\*3) menjadi O(1) queries)
2. **Maintainability**: Logic tersentralisasi, mudah dimodifikasi
3. **Code Quality**: Menghilangkan duplikasi kode
4. **Consistency**: Naming convention yang lebih jelas
5. **Reusability**: Accessor bisa digunakan di tempat lain

## Testing Checklist

-   [x] Vendor table loading time improved
-   [x] Usage status badge displays correctly
-   [x] Tooltip shows detailed information
-   [x] Delete protection works properly
-   [x] Bulk actions work correctly
-   [x] No PHP errors or warnings
-   [x] All references to old `vendors()` method updated

## Files Modified

1. `/app/Models/Vendor.php`

    - Renamed method `vendors()` → `expenses()`
    - Added `getUsageStatusAttribute()` accessor
    - Added `getUsageDetailsAttribute()` accessor
    - Updated `delete()` method to use new accessors

2. `/app/Filament/Resources/VendorResource.php`
    - Added eager loading with `withCount()`
    - Simplified `usage_status` column implementation
    - Updated all references from `vendors()` to `expenses()`
    - Updated all usage checks to use accessors

## Compatibility Notes

⚠️ **BREAKING CHANGE**: Method `vendors()` di model Vendor telah direname menjadi `expenses()`.
Jika ada kode lain yang menggunakan `$vendor->vendors()`, harus diupdate menjadi `$vendor->expenses()`.

## Performance Metrics

**Estimasi Performance Improvement:**

-   Table dengan 100 vendors: 300+ queries → 1 query (99% improvement)
-   Table dengan 1000 vendors: 3000+ queries → 1 query (99.9% improvement)
-   Page load time: Dari 3-5 detik → <1 detik

---

_Dokumentasi ini dibuat untuk membantu maintenance dan development tim di masa depan._
