# NotaDinasSeeder Documentation

## Overview

`NotaDinasSeeder` adalah seeder untuk membuat data sample Nota Dinas beserta detail-detailnya dalam sistem Makna Finance.

## Prerequisites

Sebelum menjalankan seeder ini, pastikan data berikut sudah tersedia:

-   **Users**: Minimal 2 user (untuk pengirim dan penerima)
-   **Vendors**: Minimal 1 vendor
-   **Orders**: Data order (opsional, akan menggunakan random jika ada)

## Data Structure

### Nota Dinas Records

Seeder ini akan membuat **3 Nota Dinas** dengan status yang berbeda:

1. **ND-{timestamp}-001** - Status: `disetujui`

    - Sifat: Segera
    - Hal: Permintaan Transfer Vendor Wedding
    - 1 detail: CV Dekorasi Mewah (Rp 15,000,000)

2. **ND-{timestamp}-002** - Status: `diajukan`

    - Sifat: Biasa
    - Hal: Permintaan Transfer Vendor Catering
    - 1 detail: PT Catering Lezat (Rp 25,000,000)

3. **ND-{timestamp}-003** - Status: `draft`
    - Sifat: Segera
    - Hal: Permintaan Transfer Multiple Vendor
    - 2 details: Toko Bunga Segar (Rp 5,000,000) + Studio Foto Keren (Rp 8,000,000)

### Detail Records

Total **4 detail records** akan dibuat dengan berbagai kombinasi:

| Field               | Value Options                                |
| ------------------- | -------------------------------------------- |
| `status_invoice`    | `belum_dibayar`, `menunggu`, `sudah_dibayar` |
| `jenis_pengeluaran` | `operasional`, `non_operasional`             |
| `payment_stage`     | `down_payment` (default)                     |
| `bank_name`         | BCA, Mandiri, BNI, BRI                       |

## Running the Seeder

### Command

```bash
php artisan db:seed --class=NotaDinasSeeder
```

### Prerequisites Check

```bash
# Check users count
php artisan tinker --execute="echo 'Users: ' . User::count();"

# Check vendors count
php artisan tinker --execute="echo 'Vendors: ' . Vendor::count();"
```

### Clear Existing Data (Optional)

```bash
php artisan tinker --execute="
DB::statement('SET FOREIGN_KEY_CHECKS=0');
DB::table('nota_dinas_details')->delete();
DB::table('nota_dinas')->delete();
DB::statement('SET FOREIGN_KEY_CHECKS=1');"
```

## Unique Features

### 1. Timestamp-based Unique IDs

-   Nomor ND: `ND-{YmdHis}-{sequence}`
-   Invoice: `INV-{YmdHis}-{sequence}`
-   Menghindari duplicate key errors

### 2. Smart Validation

-   Checks minimum user count (need 2+ users)
-   Checks vendor availability
-   Graceful error handling dengan informative messages

### 3. Comprehensive Output

-   Real-time progress dengan ✅ checkmarks
-   Summary table dengan metrics:
    -   Total records created
    -   Status distribution
    -   Detail count

## Sample Output

```
Creating Nota Dinas records...
✅ Created Nota Dinas: ND-20250825173802-001 with 1 details
✅ Created Nota Dinas: ND-20250825173802-002 with 1 details
✅ Created Nota Dinas: ND-20250825173802-003 with 2 details
🎉 NotaDinas seeder completed successfully!
📊 Created 3 Nota Dinas records with details

+--------------------+-------+
| Metric             | Count |
+--------------------+-------+
| Nota Dinas Records | 3     |
| Detail Records     | 4     |
| Draft Status       | 1     |
| Diajukan Status    | 1     |
| Disetujui Status   | 1     |
+--------------------+-------+
```

## Database Schema Compliance

### NotaDinas Table

-   ✅ Unique constraint pada `no_nd`
-   ✅ Foreign key ke `users` (pengirim, penerima, approved_by)
-   ✅ Enum values untuk `status` dan `sifat`

### NotaDinasDetail Table

-   ✅ Foreign key ke `nota_dinas` (cascade delete)
-   ✅ Foreign key ke `vendors` dan `orders`
-   ✅ Enum compliance:
    -   `status_invoice`: `belum_dibayar`, `menunggu`, `sudah_dibayar`
    -   `jenis_pengeluaran`: `operasional`, `non_operasional`
-   ✅ Decimal precision (18,2) untuk `jumlah_transfer`

## Error Handling

### Common Issues & Solutions

1. **Insufficient Users**

    ```
    Error: Need at least 2 users. Please run UserSeeder first.
    Solution: php artisan db:seed --class=UserSeeder
    ```

2. **No Vendors Found**

    ```
    Error: No vendors found. Please run VendorSeeder first.
    Solution: php artisan db:seed --class=VendorSeeder
    ```

3. **Duplicate Key Error**
    ```
    Error: Duplicate entry 'ND-xxx' for key 'nota_dinas_no_nd_unique'
    Solution: Clear existing data first (see command above)
    ```

## Production Usage

### Safe for Production ✅

-   Uses proper enum values
-   Follows database constraints
-   Non-destructive (doesn't clear existing data automatically)
-   Validates prerequisites

### Recommended Sequence

```bash
1. php artisan db:seed --class=UserSeeder
2. php artisan db:seed --class=VendorSeeder
3. php artisan db:seed --class=NotaDinasSeeder
```

## Development Notes

### File Location

```
database/seeders/NotaDinasSeeder.php
```

### Dependencies

-   Carbon (for timestamps)
-   Laravel Eloquent ORM
-   App\Models\NotaDinas
-   App\Models\NotaDinasDetail
-   App\Models\User
-   App\Models\Vendor
-   App\Models\Order

### Last Updated

August 25, 2025 - v1.0

---

**Status**: ✅ Production Ready
**Tested**: ✅ Successfully creates 3 NotaDinas + 4 Details
**Documentation**: ✅ Complete
