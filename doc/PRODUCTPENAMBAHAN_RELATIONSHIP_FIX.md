# ProductPenambahan Relationship Fix

## Tanggal Perbaikan

13 Oktober 2025

## Issue

Vendor yang digunakan di ProductResource dalam tab "Penambahan Harga" masih menampilkan status `Available` dan bisa dihapus, padahal seharusnya menampilkan status `In Use` dan tidak bisa dihapus.

## Root Cause Analysis

Logic `usage_status` dan `usage_details` accessor di model Vendor tidak memperhitungkan relasi `ProductPenambahan` (table yang menyimpan data penambahan harga pada produk).

## Perbaikan yang Dilakukan

### 1. **Model Vendor - Tambah Relasi Baru**

```php
public function productPenambahans(): HasMany
{
    return $this->hasMany(ProductPenambahan::class);
}
```

### 2. **Update Usage Status Accessor**

```php
public function getUsageStatusAttribute(): string
{
    $productCount = $this->productVendors_count ?? $this->productVendors()->count();
    $expenseCount = $this->expenses_count ?? $this->expenses()->count();
    $notaDinasCount = $this->nota_dinas_details_count ?? $this->notaDinasDetails()->count();
    $productPenambahanCount = $this->product_penambahans_count ?? $this->productPenambahans()->count();

    return ($productCount > 0 || $expenseCount > 0 || $notaDinasCount > 0 || $productPenambahanCount > 0)
        ? 'In Use'
        : 'Available';
}
```

### 3. **Update Usage Details Accessor**

```php
public function getUsageDetailsAttribute(): array
{
    $productCount = $this->productVendors_count ?? $this->productVendors()->count();
    $expenseCount = $this->expenses_count ?? $this->expenses()->count();
    $notaDinasCount = $this->nota_dinas_details_count ?? $this->notaDinasDetails()->count();
    $productPenambahanCount = $this->product_penambahans_count ?? $this->productPenambahans()->count();

    return [
        'productCount' => $productCount,
        'expenseCount' => $expenseCount,
        'notaDinasCount' => $notaDinasCount,
        'productPenambahanCount' => $productPenambahanCount,
    ];
}
```

### 4. **VendorResource - Eager Loading**

```php
->query(
    static::getEloquentQuery()->withCount([
        'productVendors',
        'expenses',
        'notaDinasDetails',
        'productPenambahans' // ✅ Tambahan baru
    ])
)
```

### 5. **VendorResource - Updated Tooltip**

```php
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
    if ($details['productPenambahanCount'] > 0) {
        $descriptions[] = "{$details['productPenambahanCount']} product addition(s)"; // ✅ Tambahan baru
    }

    return !empty($descriptions)
        ? 'Used in: ' . implode(', ', $descriptions)
        : 'Not used in any products, expenses, nota dinas details, or product additions';
})
```

### 6. **Delete Protection Updates**

Semua bagian delete protection (single delete, bulk delete, cannot delete action) sekarang juga memperhitungkan `productPenambahanCount`.

### 7. **View Usage Modal Enhancement**

Tambah section baru untuk menampilkan Product Penambahan:

```php
$productPenambahanCount = $usageDetails['productPenambahanCount'];
if ($productPenambahanCount > 0) {
    $productPenambahans = $record->productPenambahans()
        ->with('product')
        ->get()
        ->groupBy('product.name');

    $content .= '<div class="p-4 bg-purple-50 border border-purple-200 rounded-lg">';
    $content .= '<h3 class="font-semibold text-purple-800 mb-2">Used in Product Additions (' . $productPenambahanCount . ' items)</h3>';
    $content .= '<ul class="list-disc list-inside text-purple-700 space-y-1">';

    foreach ($productPenambahans as $productName => $items) {
        $totalAmount = $items->sum('harga_publish');
        $content .= '<li>' . $productName . ' (Total: Rp ' . number_format($totalAmount, 0, ',', '.') . ')</li>';
    }

    $content .= '</ul></div>';
}
```

## Testing Scenario

### **Before Fix:**

1. Buat produk baru
2. Tambahkan vendor di tab "Penambahan Harga"
3. Simpan produk
4. Cek vendor di table → Status: `Available` ❌
5. Vendor bisa dihapus ❌

### **After Fix:**

1. Buat produk baru
2. Tambahkan vendor di tab "Penambahan Harga"
3. Simpan produk
4. Cek vendor di table → Status: `In Use` ✅
5. Vendor tidak bisa dihapus ✅
6. Tooltip menampilkan "1 product addition(s)" ✅
7. View Usage modal menampilkan section Product Additions ✅

## Files Modified

1. **`/app/Models/Vendor.php`**

    - Tambah relasi `productPenambahans()`
    - Update `getUsageStatusAttribute()`
    - Update `getUsageDetailsAttribute()`

2. **`/app/Filament/Resources/VendorResource.php`**
    - Tambah `productPenambahans` ke eager loading
    - Update tooltip logic
    - Update delete protection logic
    - Update bulk delete logic
    - Enhance View Usage modal

## Expected Behavior

Setelah perbaikan ini:

✅ **Vendor yang digunakan di ProductResource tab "Penambahan Harga" akan:**

-   Menampilkan status `In Use` di table
-   Tidak bisa dihapus (button Delete disabled)
-   Tooltip menampilkan "X product addition(s)"
-   Modal "View Usage" menampilkan detail product additions
-   Bulk delete akan skip vendor yang digunakan

✅ **Konsistensi:**

-   Semua jenis penggunaan vendor (Products, Expenses, Nota Dinas, Product Additions) diperhitungkan
-   Logic delete protection konsisten di semua fitur
-   UI feedback yang akurat dan informatif

## Compatibility

-   ✅ Backward compatible dengan data existing
-   ✅ Tidak mengubah functionality yang ada
-   ✅ Performance tetap optimal dengan eager loading
-   ✅ Mengikuti pattern yang sudah ada

---

_Perbaikan ini memastikan integrity data dan mencegah penghapusan vendor yang masih digunakan di berbagai bagian sistem._
