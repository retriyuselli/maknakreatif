<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\Prospect;
use App\Models\Role;
use App\Models\Status;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\IndustrySeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\VendorSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\EmployeeSeeder;
use Database\Seeders\DataPribadiSeeder;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\BankStatementSeeder;
use Database\Seeders\ProspectSeeder;
use Database\Seeders\SimulasiProdukSeeder;
use Database\Seeders\OrderSeeder;
use Database\Seeders\NotaDinasSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call specific seeders in order
        $this->call([
            // First create users and statuses
            // (User and Status creation is done below)
        ]);

        Status::factory()->createMany([
            ['status_name' => 'Karyawan'],
            ['status_name' => 'Account Manager'],
            ['status_name' => 'Event Manager'],
            ['status_name' => 'Finance'],
            ['status_name' => 'Freelance'],
            ['status_name' => 'Vendor'],
            ['status_name' => 'Medsos'],
            ['status_name' => 'Admin Account Manager & Event Manager'],
        ]);

        // Call all seeders after users and statuses are created
        $this->call([
            IndustrySeeder::class,       // Master data - Industries first
            CategorySeeder::class,       // Master data - Categories
            VendorSeeder::class,         // Master data - Vendors
            ProductSeeder::class,        // Master data - Products
            EmployeeSeeder::class,       // HR data - Employees
            DataPribadiSeeder::class,    // HR data - Personal data
            PaymentMethodSeeder::class,  // Finance data - Payment methods
            BankStatementSeeder::class,  // Finance data - Bank statements
            ProspectSeeder::class,       // Business data - Prospects
            SimulasiProdukSeeder::class, // Business data - Product simulations
            OrderSeeder::class,          // Business data - Wedding orders
            ProspectAppSeeder::class,    // Business data - Prospect applications
            ExpenseOpsSeeder::class, // Business data - Operational expenses
            PendapatanLainSeeder::class, // Business data - Other incomes
            PengeluaranLainSeeder::class, // Business data - Other expenses
            NotaDinasSeeder::class,      // Finance data - Nota Dinas with details
        ]);
    }
}
