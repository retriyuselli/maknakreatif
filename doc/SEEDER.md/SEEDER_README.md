# 📚 Database Seeder Documentation - Makna Finance

Dokumentasi lengkap untuk mengelola Laravel Seeder di project Makna Finance.

## 📁 File Dokumentasi

### 1. 📖 SEEDER_DOCUMENTATION.md

**Dokumentasi lengkap dan komprehensif**

-   Pengenalan konsep seeder
-   Struktur dan best practices
-   Template dan contoh lengkap
-   Troubleshooting common issues
-   Reference commands

**Kapan digunakan**: Untuk memahami seeder secara mendalam atau ketika membuat seeder yang kompleks.

### 2. ⚡ SEEDER_QUICK_REFERENCE.md

**Quick reference dan cheat sheet**

-   Commands yang sering digunakan
-   Execution order seeder project ini
-   Pattern dan template seeder
-   Common issues & solutions
-   Quick setup commands

**Kapan digunakan**: Untuk referensi cepat sehari-hari atau setup project.

### 3. 🛠️ TEMPLATE_YourModelSeeder.php

**Template seeder siap pakai**

-   Template lengkap dengan comments
-   Checklist untuk customization
-   Contoh dependency check
-   Pattern firstOrCreate dan factory
-   Tips dan best practices inline

**Kapan digunakan**: Ketika membuat seeder baru, copy template ini dan sesuaikan.

## 🚀 Quick Start

### Untuk Developer Baru

1. Baca `SEEDER_DOCUMENTATION.md` untuk pemahaman konsep
2. Gunakan `SEEDER_QUICK_REFERENCE.md` untuk referensi sehari-hari
3. Copy `TEMPLATE_YourModelSeeder.php` ketika membuat seeder baru

### Untuk Setup Project Baru

```bash
# 1. Setup database
php artisan migrate:fresh --seed

# 2. Cek apakah data sudah ter-load
php artisan tinker --execute="
echo 'Users: ' . App\Models\User::count() . PHP_EOL;
echo 'Status: ' . App\Models\Status::count() . PHP_EOL;
echo 'Orders: ' . App\Models\Order::count() . PHP_EOL;
"
```

### Untuk Membuat Seeder Baru

```bash
# 1. Generate seeder
php artisan make:seeder ProductCategorySeeder

# 2. Copy dari template
cp database/seeders/TEMPLATE_YourModelSeeder.php database/seeders/ProductCategorySeeder.php

# 3. Edit sesuai kebutuhan (ikuti checklist di template)

# 4. Tambahkan ke DatabaseSeeder.php

# 5. Test seeder
php artisan db:seed --class=ProductCategorySeeder
```

## 📊 Current Seeders Status

### ✅ Available Seeders (Total: 20+)

#### Master Data (5)

-   `StatusSeeder` - Status karyawan/roles
-   `IndustrySeeder` - Jenis industri bisnis
-   `CategorySeeder` - Kategori produk
-   `PaymentMethodSeeder` - Metode pembayaran
-   `RoleSeeder` - User roles & permissions

#### User & HR (3)

-   `UserSeeder` - Admin + sample users
-   `EmployeeSeeder` - Data karyawan
-   `DataPribadiSeeder` - Data pribadi karyawan

#### Business (6)

-   `VendorSeeder` - Data vendor/supplier
-   `ProductSeeder` - Produk/layanan
-   `ProspectSeeder` - Calon klien
-   `ProspectAppSeeder` - Aplikasi prospect
-   `OrderSeeder` - Order wedding
-   `SimulasiProdukSeeder` - Simulasi produk

#### Financial (6)

-   `BankStatementSeeder` - Statement bank
-   `NotaDinasSeeder` - Nota dinas transfer
-   `ExpenseOpsSeeder` - Pengeluaran operasional
-   `PendapatanLainSeeder` - Pendapatan lain
-   `PengeluaranLainSeeder` - Pengeluaran lain
-   `AccountManagerTargetSeeder` - Target AM

#### Content (3)

-   `BlogSeeder` - Artikel blog
-   `SopSeeder` - Standard Operating Procedure
-   `SopCategorySeeder` - Kategori SOP

## ⚡ Common Commands

```bash
# Development
php artisan migrate:fresh --seed    # Fresh setup
php artisan db:seed                 # Run all seeders
php artisan db:seed --class=Name    # Run specific seeder

# Production
php artisan migrate --seed          # Migrate + seed (safe)
php artisan migrate:status          # Check migration status

# Troubleshooting
composer dump-autoload             # Refresh class autoload
php artisan optimize:clear          # Clear all caches
```

## 🎯 Best Practices Summary

### ✅ DO

-   **Follow execution order** - Master data → Users → Business data
-   **Use firstOrCreate** - Prevent duplicates when re-running
-   **Check dependencies** - Verify parent data exists before creating child
-   **Provide feedback** - Use informative command outputs
-   **Use factories for bulk data** - More realistic test data

### ❌ DON'T

-   **Ignore dependency order** - Will cause foreign key errors
-   **Hardcode foreign keys** - Use relationships instead
-   **Create without checking** - Use firstOrCreate instead of create
-   **Forget to test** - Always test individual seeders first

## 🔧 Troubleshooting Quick Fixes

| Error                  | Quick Fix                                   |
| ---------------------- | ------------------------------------------- |
| Foreign key constraint | Check seeder execution order                |
| Duplicate entry        | Use `firstOrCreate()` instead of `create()` |
| Class not found        | Run `composer dump-autoload`                |
| Model not found        | Check namespace and model exists            |

## 📞 Support

Jika mengalami kesulitan:

1. **Cek log error**: `storage/logs/laravel.log`
2. **Baca dokumentasi**: File dokumentasi di atas
3. **Test manual**: Gunakan `php artisan tinker` untuk debugging
4. **Cek database**: Pastikan structure table sesuai dengan seeder

---

**📝 Note**: Dokumentasi ini akan diupdate seiring dengan penambahan seeder baru di project.

**🔄 Last Updated**: September 2, 2025
