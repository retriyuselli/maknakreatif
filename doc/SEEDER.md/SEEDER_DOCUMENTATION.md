# 📖 Dokumentasi Laravel Seeder - Makna Finance

## 📋 Daftar Isi

1. [Pengenalan Seeder](#pengenalan-seeder)
2. [Struktur Seeder](#struktur-seeder)
3. [Perintah Dasar](#perintah-dasar)
4. [Daftar Seeder yang Tersedia](#daftar-seeder-yang-tersedia)
5. [Cara Membuat Seeder Baru](#cara-membuat-seeder-baru)
6. [Best Practices](#best-practices)
7. [Troubleshooting](#troubleshooting)

---

## 🎯 Pengenalan Seeder

Seeder adalah kelas Laravel yang digunakan untuk mengisi database dengan data dummy atau data awal yang diperlukan untuk aplikasi. Dalam project Makna Finance, seeder digunakan untuk:

-   **Master Data**: Status, Industry, Category, dll
-   **User Data**: Admin user, sample users
-   **Business Data**: Orders, Prospects, Vendors
-   **Financial Data**: Bank statements, Payment methods
-   **HR Data**: Employees, Personal data

---

## 🏗️ Struktur Seeder

### DatabaseSeeder.php (Main Seeder)

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Urutan pemanggilan seeder sangat penting!
        $this->call([
            StatusSeeder::class,         // ✅ Master data dulu
            UserSeeder::class,           // ✅ User setelah status
            IndustrySeeder::class,       // ✅ Master data
            VendorSeeder::class,         // ✅ Depends on users
            OrderSeeder::class,          // ✅ Depends on users & vendors
        ]);
    }
}
```

### Template Seeder Individual

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\YourModel;

class YourModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Method 1: Array data
        $data = [
            ['field1' => 'value1', 'field2' => 'value2'],
            ['field1' => 'value3', 'field2' => 'value4'],
        ];

        foreach ($data as $item) {
            YourModel::create($item);
        }

        // Method 2: Factory
        YourModel::factory(10)->create();

        // Method 3: firstOrCreate (prevent duplicates)
        YourModel::firstOrCreate(
            ['unique_field' => 'value'],
            ['other_field' => 'other_value']
        );

        // Output info (optional)
        $this->command->info('✅ YourModel seeder completed!');
    }
}
```

---

## ⚡ Perintah Dasar

### 🔄 Menjalankan Seeder

```bash
# Jalankan semua seeder
php artisan db:seed

# Jalankan seeder tertentu
php artisan db:seed --class=StatusSeeder

# Reset database dan jalankan seeder
php artisan migrate:fresh --seed

# Jalankan migration dan seeder
php artisan migrate --seed
```

### 🆕 Membuat Seeder Baru

```bash
# Buat seeder baru
php artisan make:seeder ProductCategorySeeder

# Buat seeder dengan model
php artisan make:seeder ProductCategorySeeder --model=ProductCategory
```

### 🗑️ Reset Database

```bash
# Reset semua data dan migration
php artisan migrate:fresh

# Reset dan jalankan seeder
php artisan migrate:fresh --seed

# Rollback migration
php artisan migrate:rollback
```

---

## 📊 Daftar Seeder yang Tersedia

### 👥 Master Data Seeders

| Seeder                | Model         | Deskripsi                             | Dependencies |
| --------------------- | ------------- | ------------------------------------- | ------------ |
| `StatusSeeder`        | Status        | Status karyawan (Admin, Finance, dll) | -            |
| `IndustrySeeder`      | Industry      | Jenis industri bisnis                 | -            |
| `CategorySeeder`      | Category      | Kategori produk/layanan               | -            |
| `PaymentMethodSeeder` | PaymentMethod | Metode pembayaran                     | -            |
| `RoleSeeder`          | Role          | Role/permission system                | -            |

### 👤 User & HR Seeders

| Seeder              | Model       | Deskripsi                   | Dependencies |
| ------------------- | ----------- | --------------------------- | ------------ |
| `UserSeeder`        | User        | Admin user dan sample users | StatusSeeder |
| `EmployeeSeeder`    | Employee    | Data karyawan               | UserSeeder   |
| `DataPribadiSeeder` | DataPribadi | Data pribadi karyawan       | UserSeeder   |

### 🏢 Business Seeders

| Seeder                 | Model          | Deskripsi            | Dependencies               |
| ---------------------- | -------------- | -------------------- | -------------------------- |
| `VendorSeeder`         | Vendor         | Data vendor/supplier | UserSeeder                 |
| `ProductSeeder`        | Product        | Produk/layanan       | CategorySeeder             |
| `ProspectSeeder`       | Prospect       | Calon klien          | UserSeeder                 |
| `ProspectAppSeeder`    | ProspectApp    | Aplikasi prospect    | IndustrySeeder             |
| `OrderSeeder`          | Order          | Order wedding        | UserSeeder, ProspectSeeder |
| `SimulasiProdukSeeder` | SimulasiProduk | Simulasi produk      | ProductSeeder              |

### 💰 Financial Seeders

| Seeder                  | Model           | Deskripsi               | Dependencies             |
| ----------------------- | --------------- | ----------------------- | ------------------------ |
| `BankStatementSeeder`   | BankStatement   | Statement bank          | UserSeeder               |
| `NotaDinasSeeder`       | NotaDinas       | Nota dinas transfer     | UserSeeder, VendorSeeder |
| `ExpenseOpsSeeder`      | ExpenseOps      | Pengeluaran operasional | UserSeeder               |
| `PendapatanLainSeeder`  | PendapatanLain  | Pendapatan lain         | UserSeeder               |
| `PengeluaranLainSeeder` | PengeluaranLain | Pengeluaran lain        | UserSeeder               |

### 📋 Content Seeders

| Seeder                       | Model                | Deskripsi                    | Dependencies      |
| ---------------------------- | -------------------- | ---------------------------- | ----------------- |
| `BlogSeeder`                 | Blog                 | Artikel blog                 | UserSeeder        |
| `SopSeeder`                  | Sop                  | Standard Operating Procedure | SopCategorySeeder |
| `SopCategorySeeder`          | SopCategory          | Kategori SOP                 | -                 |
| `AccountManagerTargetSeeder` | AccountManagerTarget | Target AM                    | UserSeeder        |

---

## 🆕 Cara Membuat Seeder Baru

### Step 1: Generate Seeder

```bash
php artisan make:seeder NewModelSeeder
```

### Step 2: Edit Seeder File

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NewModel;

class NewModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cek dependency dulu
        if (Model::count() === 0) {
            $this->command->error('Dependency not found. Run DependencySeeder first.');
            return;
        }

        $this->command->info('Creating NewModel records...');

        $data = [
            [
                'name' => 'Sample Name 1',
                'description' => 'Sample Description 1',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sample Name 2',
                'description' => 'Sample Description 2',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $item) {
            NewModel::firstOrCreate(
                ['name' => $item['name']], // Unique identifier
                $item
            );
        }

        $this->command->info('✅ NewModel seeder completed!');
        $this->command->info('📊 Created ' . count($data) . ' records');
        $this->command->info('🔍 Total records: ' . NewModel::count());
    }
}
```

### Step 3: Daftarkan di DatabaseSeeder

```php
// database/seeders/DatabaseSeeder.php
public function run(): void
{
    $this->call([
        // ... seeder lain
        NewModelSeeder::class,  // ✅ Tambahkan di sini
    ]);
}
```

### Step 4: Test Seeder

```bash
# Test seeder individual
php artisan db:seed --class=NewModelSeeder

# Test dengan fresh migration
php artisan migrate:fresh --seed
```

---

## 🎯 Best Practices

### ✅ DO (Lakukan)

#### 1. **Urutan Dependency**

```php
// ✅ BENAR: Master data dulu
$this->call([
    StatusSeeder::class,      // Master data
    UserSeeder::class,        // Depends on Status
    VendorSeeder::class,      // Depends on User
    OrderSeeder::class,       // Depends on User & Vendor
]);
```

#### 2. **Gunakan firstOrCreate untuk Unique Data**

```php
// ✅ BENAR: Cegah duplicate
Status::firstOrCreate(
    ['status_name' => 'Admin'],
    ['description' => 'Administrator role']
);
```

#### 3. **Cek Dependency**

```php
// ✅ BENAR: Cek dependency sebelum create
if (User::count() === 0) {
    $this->command->error('Users not found. Run UserSeeder first.');
    return;
}
```

#### 4. **Gunakan Factory untuk Data Banyak**

```php
// ✅ BENAR: Factory untuk data banyak
User::factory(50)->create();
```

#### 5. **Berikan Feedback**

```php
// ✅ BENAR: Informative output
$this->command->info('✅ Seeder completed!');
$this->command->info('📊 Created ' . count($data) . ' records');
```

### ❌ DON'T (Jangan)

#### 1. **Jangan Abaikan Urutan**

```php
// ❌ SALAH: Order sebelum User
$this->call([
    OrderSeeder::class,       // Error: user_id not found
    UserSeeder::class,
]);
```

#### 2. **Jangan Hardcode ID**

```php
// ❌ SALAH: Hardcode foreign key
Order::create([
    'user_id' => 1,           // ID mungkin tidak ada
]);

// ✅ BENAR: Ambil dari relasi
Order::create([
    'user_id' => User::first()->id,
]);
```

#### 3. **Jangan Create Duplicate tanpa Check**

```php
// ❌ SALAH: Create langsung (duplicate error)
Status::create(['status_name' => 'Admin']);

// ✅ BENAR: Check dulu
Status::firstOrCreate(['status_name' => 'Admin']);
```

---

## 🔧 Troubleshooting

### ❗ Error: Foreign Key Constraint

```bash
Error: Cannot add or update a child row: foreign key constraint fails
```

**Solusi:**

-   Cek urutan seeder di `DatabaseSeeder.php`
-   Pastikan parent data sudah ada sebelum create child data

### ❗ Error: Duplicate Entry

```bash
Error: Duplicate entry 'value' for key 'unique_key'
```

**Solusi:**

-   Gunakan `firstOrCreate` instead of `create`
-   Atau gunakan `updateOrCreate` jika perlu update

### ❗ Error: Class Not Found

```bash
Error: Class 'Database\Seeders\YourSeeder' not found
```

**Solusi:**

-   Run `composer dump-autoload`
-   Cek namespace seeder sudah benar

### ❗ Error: Model Not Found

```bash
Error: Class 'App\Models\YourModel' not found
```

**Solusi:**

-   Pastikan model sudah dibuat
-   Cek namespace model
-   Run `composer dump-autoload`

---

## 📚 Contoh Seeder Lengkap

### Simple Seeder (Master Data)

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Status;

class StatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['status_name' => 'Karyawan'],
            ['status_name' => 'Account Manager'],
            ['status_name' => 'Event Manager'],
            ['status_name' => 'Finance'],
        ];

        foreach ($statuses as $status) {
            Status::firstOrCreate($status);
        }

        $this->command->info('✅ Status seeder completed!');
    }
}
```

### Complex Seeder (Dengan Relasi)

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\User;
use App\Models\Prospect;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // Cek dependency
        if (User::count() === 0) {
            $this->command->error('Users not found. Run UserSeeder first.');
            return;
        }

        if (Prospect::count() === 0) {
            $this->command->error('Prospects not found. Run ProspectSeeder first.');
            return;
        }

        $this->command->info('Creating orders...');

        $users = User::all();
        $prospects = Prospect::all();

        for ($i = 0; $i < 10; $i++) {
            Order::create([
                'name' => 'Wedding Event ' . ($i + 1),
                'user_id' => $users->random()->id,
                'prospect_id' => $prospects->random()->id,
                'total_price' => rand(50000000, 200000000),
                'status' => ['pending', 'processing', 'completed'][rand(0, 2)],
                'created_at' => now()->subDays(rand(1, 30)),
            ]);
        }

        $this->command->info('✅ Order seeder completed!');
        $this->command->info('📊 Created 10 orders');
    }
}
```

---

## 🚀 Quick Commands Reference

```bash
# Buat seeder baru
php artisan make:seeder NamaSeeder

# Jalankan semua seeder
php artisan db:seed

# Jalankan seeder tertentu
php artisan db:seed --class=NamaSeeder

# Reset database + seeder
php artisan migrate:fresh --seed

# Refresh composer autoload
composer dump-autoload

# Lihat status migration
php artisan migrate:status
```

---

## 📞 Support

Jika ada pertanyaan atau masalah dengan seeder:

1. **Cek log error** di `storage/logs/laravel.log`
2. **Cek urutan dependency** di `DatabaseSeeder.php`
3. **Pastikan model dan relasi sudah benar**
4. **Gunakan `php artisan tinker`** untuk testing manual

---

**📝 Note**: Dokumentasi ini dibuat untuk memudahkan development dan maintenance seeder di project Makna Finance. Update dokumentasi ini jika ada perubahan atau penambahan seeder baru.

**🔄 Last Updated**: September 2, 2025
