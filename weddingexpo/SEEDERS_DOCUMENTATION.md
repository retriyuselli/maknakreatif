# Database Seeders Documentation - Makna Wedding Organizer

## Overview

This document provides comprehensive information about all database seeders used in the Makna Wedding Organizer Laravel application. Seeders are used to populate the database with initial and test data for development and testing purposes.

## Table of Contents

Buat dulu Users, Status, Category, Vendor, Product, Employee, DataPribadi, PaymentMethod, Role, BankStatement, Prospect, dan SimulasiProduk.

1. [Quick Commands](#quick-commands)
2. [Seeder Execution Order](#seeder-execution-order)
3. [Individual Seeders](#individual-seeders)
4. [Troubleshooting](#troubleshooting)
5. [Best Practices](#best-practices)

## Quick Commands

### Run All Seeders

```bash
# Fresh migration with all seeders
php artisan migrate:fresh --seed

# Only run seeders (without migration)
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=SeederName
```

### Individual Seeder Commands

```bash
# Run specific seeders
php artisan db:seed --class=RoleSeeder
php artisan shield:generate --all
php artisan db:seed --class=DatabaseSeeder
php artisan db:seed --class=IndustrySeeder
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=VendorSeeder
php artisan db:seed --class=ProductSeeder
php artisan db:seed --class=EmployeeSeeder
php artisan db:seed --class=DataPribadiSeeder
php artisan db:seed --class=PaymentMethodSeeder
php artisan db:seed --class=BankStatementSeeder
php artisan db:seed --class=ProspectSeeder
php artisan db:seed --class=SimulasiProdukSeeder
php artisan db:seed --class=ProspectAppSeeder
php artisan db:seed --class=OrderSeeder
php artisan db:seed --class=UserSeeder
```

### Development Commands

```bash
# Reset database and run fresh seeders
php artisan migrate:fresh --seed

# Reset specific tables and reseed
php artisan migrate:refresh --seed

# Check seeder status
php artisan migrate:status
```

## Seeder Execution Order

The seeders must be executed in the following order due to foreign key dependencies:

1. **Users & Status** (created in DatabaseSeeder)
2. **IndustriSeeder** - Industry categories for business classification
3. **CategorySeeder** - Product categories
4. **VendorSeeder** - Vendor/supplier data
5. **ProductSeeder** - Wedding service products
6. **EmployeeSeeder** - Employee hierarchy
7. **DataPribadiSeeder** - Employee personal data
8. **PaymentMethodSeeder** - Bank accounts and payment methods
9. **BankStatementSeeder** - Bank statement records
10. **ProspectSeeder** - Potential customer data
11. **SimulasiProdukSeeder** - Product simulations
12. **RoleSeeder** - Role-based access control

## Individual Seeders

### 1. DatabaseSeeder

**File:** `database/seeders/DatabaseSeeder.php`
**Purpose:** Main seeder that orchestrates all other seeders
**Dependencies:** None (creates base data)

**What it creates:**

-   7 system users with different roles
-   8 status types for classification
-   Calls all other seeders in correct order

**Users created:**

-   Admin Utama (admin@example.com / admin123)
-   Marketing Leader (marketing@example.com / marketing123)
-   Finance Staff (finance@example.com / finance123)
-   HRD Staff (hrd@example.com / hrd123)
-   IT Support (it@example.com / it123)
-   Sales Executive (sales@example.com / sales123)
-   Customer Service (cs@example.com / cs123)

### 2. IndustriSeeder

**File:** `database/seeders/IndustriSeeder.php`
**Purpose:** Creates industry categories for business classification
**Dependencies:** None
**Count:** 12 wedding-related industry categories

**What it creates:**

-   Industry categories for wedding and event planning businesses
-   Business type classifications for prospect segmentation
-   Master data for industry selection in forms

**Industries created:**

-   Wedding Organizer
-   Event Organizer
-   Photography & Videography
-   Bridal / Makeup Artist
-   Dekorasi Pernikahan
-   Venue Pernikahan
-   Katering Pernikahan
-   Hiburan (MC, Band, DJ)
-   Percetakan Undangan / Souvenir
-   Busana Pengantin
-   Perhiasan / Cincin
-   Lainnya

**Command:**

```bash
php artisan db:seed --class=IndustriSeeder
```

### 3. CategorySeeder

**File:** `database/seeders/CategorySeeder.php`
**Purpose:** Creates product/service categories
**Dependencies:** None
**Count:** Variable (wedding service categories)

**Command:**

```bash
php artisan db:seed --class=CategorySeeder
```

### 4. VendorSeeder

**File:** `database/seeders/VendorSeeder.php`
**Purpose:** Creates vendor/supplier data for wedding services
**Dependencies:** Status model
**Count:** Multiple vendors

**Command:**

```bash
php artisan db:seed --class=VendorSeeder
```

### 5. ProductSeeder

**File:** `database/seeders/ProductSeeder.php`
**Purpose:** Creates wedding service products/packages
**Dependencies:** Category, Vendor models
**Count:** Various wedding packages and services

**Command:**

```bash
php artisan db:seed --class=ProductSeeder
```

### 6. EmployeeSeeder

**File:** `database/seeders/EmployeeSeeder.php`
**Purpose:** Creates comprehensive employee hierarchy for wedding organizer business
**Dependencies:** User model for account assignments
**Count:** 10 employees

**What it creates:**

-   Organizational hierarchy from Founder to Junior Crew
-   Realistic salary structures (Rp 2,500,000 - Rp 20,000,000)
-   Bank account details for each employee
-   Position-based responsibilities

**Employee positions:**

1. Founder & CEO
2. General Manager
3. Marketing Manager
4. Finance Manager
5. Senior Wedding Planner
6. Wedding Coordinator
7. Event Decorator
8. Photography Coordinator
9. Audio Visual Technician
10. Junior Event Crew

**Command:**

```bash
php artisan db:seed --class=EmployeeSeeder
```

### 7. DataPribadiSeeder

**File:** `database/seeders/DataPribadiSeeder.php`
**Purpose:** Creates comprehensive team member profiles with training history
**Dependencies:** None (standalone team data)
**Count:** 10 detailed profiles

**What it creates:**

-   Professional development tracking
-   Career motivations and goals
-   Rich HTML-formatted training content
-   Personal growth documentation

**Command:**

```bash
php artisan db:seed --class=DataPribadiSeeder
```

### 8. PaymentMethodSeeder

**File:** `database/seeders/PaymentMethodSeeder.php`
**Purpose:** Creates financial account structure for the business
**Dependencies:** None
**Count:** 3 payment methods

**What it creates:**

-   BCA Business Account (Rp 50,000,000 opening balance)
-   Bank Mandiri Account (Rp 30,000,000 opening balance)
-   Cash Account (Rp 5,000,000 opening balance)

**Account details:**

-   Complete bank information with account numbers
-   Realistic opening balances for business operations
-   Account holder information

**Command:**

```bash
php artisan db:seed --class=PaymentMethodSeeder
```

### 9. BankStatementSeeder

**File:** `database/seeders/BankStatementSeeder.php`
**Purpose:** Creates bank statement records for financial tracking
**Dependencies:** PaymentMethodSeeder (requires payment methods)
**Count:** 4 bank statements

**What it creates:**

-   Monthly bank statements for BCA and Mandiri accounts
-   Transaction summaries and balance calculations
-   2-month period coverage for testing
-   Realistic business transaction patterns

**Command:**

```bash
php artisan db:seed --class=BankStatementSeeder
```

### 10. ProspectSeeder

**File:** `database/seeders/ProspectSeeder.php`
**Purpose:** Creates potential customer/prospect data
**Dependencies:** User model
**Count:** Multiple wedding prospects

**Command:**

```bash
php artisan db:seed --class=ProspectSeeder
```

### 11. SimulasiProdukSeeder

**File:** `database/seeders/SimulasiProdukSeeder.php`
**Purpose:** Creates product simulation data for testing
**Dependencies:** Product model
**Count:** Various product simulations

**Command:**

```bash
php artisan db:seed --class=SimulasiProdukSeeder
```

## Removed Seeders

### OrderSeeder (REMOVED)

**Status:** REMOVED per user request to restore pre-OrderSeeder state
**Previous Purpose:** Created comprehensive order ecosystem with items, payments, expenses
**Removal Date:** As per conversation history
**Note:** All associated order data was also removed during restoration

## Special Cases and Notes

### RoleSeeder

**File:** `database/seeders/RoleSeeder.php`
**Purpose:** Creates user roles and permissions (if using role-based access)
**Status:** Available but not in main execution chain

### SimulasiProdukSeederFresh

**File:** `database/seeders/SimulasiProdukSeederFresh.php`
**Purpose:** Alternative version of SimulasiProdukSeeder
**Status:** Available as backup/alternative

## Database State After Full Seeding

### Users: 7 system users

-   Complete authentication system ready
-   Different role assignments for testing

### Financial System: 3 payment methods + 4 bank statements

-   Ready for payment processing
-   Historical financial data for reporting

### HR System: 10 employees + 10 personal profiles

-   Complete organizational structure
-   Training and development tracking

### Business Operations: Categories, Vendors, Products, Prospects

-   Complete wedding service catalog
-   Vendor network established
-   Customer pipeline ready

### Order System: Restored to clean state

-   Only 1 original order remains (ORD-0001 - Wedding Andi & Sari)
-   No test orders or associated data

## Troubleshooting

### Common Issues

1. **Foreign Key Constraint Errors**

    ```bash
    # Solution: Run seeders in correct order
    php artisan migrate:fresh --seed
    ```

2. **Duplicate Entry Errors**

    ```bash
    # Solution: Reset database first
    php artisan migrate:fresh --seed
    ```

3. **Memory Limit Issues**

    ```bash
    # Solution: Increase PHP memory limit
    php -d memory_limit=512M artisan db:seed
    ```

4. **Permission Errors**
    ```bash
    # Solution: Check file permissions
    chmod 755 database/seeders/
    ```

### Verification Commands

```bash
# Check seeded data counts
php artisan tinker
>>> \App\Models\User::count()
>>> \App\Models\Employee::count()
>>> \App\Models\PaymentMethod::count()
>>> \App\Models\BankStatement::count()
>>> \App\Models\DataPribadi::count()
>>> exit
```

### Database Reset Commands

```bash
# Complete reset with fresh seeders
php artisan migrate:fresh --seed

# Reset specific table
php artisan migrate:refresh --path=database/migrations/xxxx_create_table_name.php

# Rollback and re-run specific seeder
php artisan db:seed --class=SeederName
```

## Best Practices

### Development Workflow

1. Always use `migrate:fresh --seed` for complete reset
2. Test individual seeders before adding to DatabaseSeeder
3. Maintain realistic test data that reflects business scenarios
4. Document any new seeders in this file

### Production Considerations

1. Never run seeders in production environment
2. Use factories for large datasets in testing
3. Keep seeder data minimal but comprehensive
4. Backup database before major seeder changes

### Data Integrity

1. Follow foreign key dependencies order
2. Use consistent data formats (phone numbers, emails, etc.)
3. Maintain realistic business relationships
4. Test seeder independence (ability to run individually)

## Seeder Status Summary

✅ **Active Seeders (Ready for use):**

-   DatabaseSeeder (Users + Status + orchestration)
-   IndustriSeeder (12 wedding-related industry categories)
-   CategorySeeder
-   VendorSeeder
-   ProductSeeder
-   EmployeeSeeder (10 employees with hierarchy)
-   DataPribadiSeeder (10 profiles with training data)
-   PaymentMethodSeeder (3 financial accounts)
-   BankStatementSeeder (4 monthly statements)
-   ProspectSeeder
-   SimulasiProdukSeeder

❌ **Removed Seeders:**

-   OrderSeeder (removed per user request for database restoration)

⚠️ **Available but Unused:**

-   RoleSeeder (role-based access control)
-   SimulasiProdukSeederFresh (alternative version)

## File Locations

All seeder files are located in:

```
database/seeders/
├── DatabaseSeeder.php (main orchestrator)
├── CategorySeeder.php
├── VendorSeeder.php
├── ProductSeeder.php
├── EmployeeSeeder.php
├── DataPribadiSeeder.php
├── PaymentMethodSeeder.php
├── BankStatementSeeder.php
├── ProspectSeeder.php
├── SimulasiProdukSeeder.php
├── RoleSeeder.php
└── SimulasiProdukSeederFresh.php
```

## Last Updated

Document created: July 11, 2025
Database state: Post-OrderSeeder removal (clean state)
Project: Makna Wedding Organizer Laravel 11 + Filament

---

For additional support or questions about seeders, refer to the Laravel documentation or contact the development team.
