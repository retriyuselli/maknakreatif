# 📋 Dokumentasi Seeder Makna Finance - Quick Reference

## 🎯 Seeder Commands untuk Project Makna Finance

### 🔄 Production Setup (Fresh Install)

```bash
# Setup database dari awal
php artisan migrate:fresh --seed

# Atau step by step
php artisan migrate:fresh
php artisan db:seed
```

### 🧪 Development Testing

```bash
# Reset data untuk testing
php artisan migrate:fresh --seed

# Jalankan seeder tertentu saja
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=StatusSeeder
php artisan db:seed --class=OrderSeeder
```

---

## 📊 Seeder Execution Order (PENTING!)

```php
// DatabaseSeeder.php - Urutan ini HARUS diikuti!
$this->call([
    // 1. Master Data (tidak ada dependency)
    StatusSeeder::class,           // ✅ Roles: Karyawan, Admin, Finance, etc
    IndustrySeeder::class,         // ✅ Industries: Wedding, Photography, etc
    CategorySeeder::class,         // ✅ Product categories
    PaymentMethodSeeder::class,    // ✅ Payment methods: Bank Transfer, Cash, etc
    RoleSeeder::class,             // ✅ User roles & permissions

    // 2. User Data (depends on Status)
    UserSeeder::class,             // ✅ Admin user + sample users

    // 3. Business Master Data (depends on User)
    VendorSeeder::class,           // ✅ Suppliers (user_id foreign key)
    ProductSeeder::class,          // ✅ Products/services

    // 4. HR Data (depends on User)
    EmployeeSeeder::class,         // ✅ Employee records
    DataPribadiSeeder::class,      // ✅ Personal data

    // 5. Financial Data (depends on User)
    BankStatementSeeder::class,    // ✅ Bank transactions

    // 6. Business Data (depends on User + Master Data)
    ProspectSeeder::class,         // ✅ Potential clients
    ProspectAppSeeder::class,      // ✅ Prospect applications
    SimulasiProdukSeeder::class,   // ✅ Product simulations

    // 7. Operational Data (depends on Prospects + Users)
    OrderSeeder::class,            // ✅ Wedding orders
    ExpenseOpsSeeder::class,       // ✅ Operational expenses
    PendapatanLainSeeder::class,   // ✅ Other income
    PengeluaranLainSeeder::class,  // ✅ Other expenses

    // 8. Complex Data (depends on multiple tables)
    NotaDinasSeeder::class,        // ✅ Transfer notes (User + Vendor + Order)
]);
```

---

## 🏗️ Template Seeder Patterns

### Pattern 1: Simple Master Data

```php
<?php
// StatusSeeder.php - Contoh seeder master data sederhana

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
            ['status_name' => 'Freelance'],
            ['status_name' => 'Vendor'],
            ['status_name' => 'Medsos'],
            ['status_name' => 'Admin Account Manager & Event Manager'],
        ];

        foreach ($statuses as $status) {
            Status::firstOrCreate($status);
        }

        $this->command->info('✅ Status seeder completed!');
    }
}
```

### Pattern 2: Complex Data dengan Relasi

```php
<?php
// OrderSeeder.php - Contoh seeder dengan foreign key

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\User;
use App\Models\Prospect;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // ✅ SELALU cek dependency dulu
        if (User::count() === 0) {
            $this->command->error('❌ No users found. Run UserSeeder first.');
            return;
        }

        if (Prospect::count() === 0) {
            $this->command->error('❌ No prospects found. Run ProspectSeeder first.');
            return;
        }

        $this->command->info('🔄 Creating wedding orders...');

        $users = User::all();
        $prospects = Prospect::take(10)->get(); // Ambil 10 prospect pertama

        $orders = [
            [
                'name' => 'Wedding Andika & Sari',
                'slug' => 'wedding-andika-sari',
                'prospect_id' => $prospects->first()->id,
                'user_id' => $users->where('email', 'admin@example.com')->first()->id,
                'total_price' => 150000000,
                'status' => 'processing',
            ],
            [
                'name' => 'Wedding Budi & Citra',
                'slug' => 'wedding-budi-citra',
                'prospect_id' => $prospects->skip(1)->first()->id,
                'user_id' => $users->random()->id,
                'total_price' => 200000000,
                'status' => 'completed',
            ],
            // ... tambah data lain
        ];

        foreach ($orders as $orderData) {
            Order::firstOrCreate(
                ['slug' => $orderData['slug']], // Unique identifier
                $orderData
            );
        }

        $this->command->info('✅ Order seeder completed!');
        $this->command->info('📊 Created ' . count($orders) . ' wedding orders');
        $this->command->info('🔍 Total orders: ' . Order::count());
    }
}
```

### Pattern 3: Factory + Manual Data

```php
<?php
// UserSeeder.php - Kombinasi manual data + factory

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Status;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ✅ Cek dependency
        if (Status::count() === 0) {
            $this->command->error('❌ No statuses found. Run StatusSeeder first.');
            return;
        }

        $adminStatus = Status::where('status_name', 'Admin')->first();
        $employeeStatus = Status::where('status_name', 'Karyawan')->first();

        // 1. Buat admin user (manual)
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'status_id' => $adminStatus?->id,
                'email_verified_at' => now(),
            ]
        );

        // 2. Buat sample users (factory)
        User::factory(10)->create([
            'status_id' => $employeeStatus?->id,
        ]);

        $this->command->info('✅ User seeder completed!');
        $this->command->info('👤 Created admin user: admin@example.com');
        $this->command->info('👥 Created 10 sample users');
        $this->command->info('🔍 Total users: ' . User::count());
    }
}
```

---

## 🎯 Data Samples dari Project

### Status Data (StatusSeeder)

```php
$statuses = [
    ['status_name' => 'Karyawan'],
    ['status_name' => 'Account Manager'],
    ['status_name' => 'Event Manager'],
    ['status_name' => 'Finance'],
    ['status_name' => 'Freelance'],
    ['status_name' => 'Vendor'],
    ['status_name' => 'Medsos'],
    ['status_name' => 'Admin Account Manager & Event Manager'],
];
```

### Industry Data (IndustrySeeder)

```php
$industries = [
    [
        'industry_name' => 'Wedding Organizer',
        'description' => 'Layanan perencanaan dan koordinasi acara pernikahan',
        'is_active' => true,
    ],
    [
        'industry_name' => 'Event Organizer',
        'description' => 'Layanan perencanaan dan penyelenggaraan berbagai jenis acara',
        'is_active' => true,
    ],
    [
        'industry_name' => 'Photography & Videography',
        'description' => 'Layanan fotografi dan videografi untuk berbagai kebutuhan',
        'is_active' => true,
    ],
    // ... 9 industries lainnya
];
```

### Payment Method Data (PaymentMethodSeeder)

```php
$paymentMethods = [
    ['name' => 'Bank Transfer'],
    ['name' => 'Cash'],
    ['name' => 'Credit Card'],
    ['name' => 'Debit Card'],
    ['name' => 'E-Wallet'],
    ['name' => 'Check'],
];
```

---

## 🚀 Quick Setup Commands

### Fresh Installation

```bash
# 1. Setup environment
cp .env.example .env
php artisan key:generate

# 2. Database setup
php artisan migrate:fresh --seed

# 3. Storage link
php artisan storage:link

# 4. Clear caches
php artisan optimize:clear
```

### Add New Seeder

```bash
# 1. Buat seeder
php artisan make:seeder NewTableSeeder

# 2. Edit file seeder di database/seeders/

# 3. Tambahkan ke DatabaseSeeder.php

# 4. Test seeder
php artisan db:seed --class=NewTableSeeder

# 5. Test full seeder
php artisan migrate:fresh --seed
```

---

## ⚠️ Common Issues & Solutions

### Issue 1: Foreign Key Constraint Error

```bash
SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row
```

**Solution:**

-   Cek urutan seeder di `DatabaseSeeder.php`
-   Pastikan parent table sudah ada data sebelum create child

### Issue 2: Duplicate Entry Error

```bash
SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry
```

**Solution:**

-   Gunakan `firstOrCreate()` instead of `create()`
-   Atau `updateOrCreate()` jika perlu update existing data

### Issue 3: Class Not Found

```bash
Class 'Database\Seeders\YourSeeder' not found
```

**Solution:**

```bash
composer dump-autoload
php artisan optimize:clear
```

---

## 🔍 Testing Commands

```bash
# Cek data after seeding
php artisan tinker

# Di tinker console:
>>> User::count()
>>> Status::all()
>>> Order::with('user', 'prospect')->get()
>>> exit
```

---

## 📱 Quick Status Check

```bash
# Cek database connection
php artisan migrate:status

# Cek apakah seeder sudah jalan
php artisan tinker --execute="
echo 'Users: ' . App\Models\User::count() . PHP_EOL;
echo 'Status: ' . App\Models\Status::count() . PHP_EOL;
echo 'Orders: ' . App\Models\Order::count() . PHP_EOL;
echo 'Prospects: ' . App\Models\Prospect::count() . PHP_EOL;
"
```

---

**🎯 Remember**: Selalu jalankan seeder dalam urutan yang benar untuk menghindari foreign key errors!

**📝 Last Updated**: September 2, 2025
