# NotaDinasDetail Seeder Documentation

## 📊 Overview

Seeder untuk NotaDinasDetail dengan pembagian yang seimbang untuk setiap jenis pengeluaran.

## 🎯 Data Distribution

-   **Wedding**: 10 records
-   **Operasional**: 10 records
-   **Lain-lain**: 10 records
-   **Total**: 30 records

## 💰 Sample Data

### 1. Wedding Expenses (10 items)

| Keperluan                | Payment Stage | Amount       |
| ------------------------ | ------------- | ------------ |
| Dekorasi Pelaminan       | DP            | Rp 2.500.000 |
| Catering Wedding         | Payment 1     | Rp 8.000.000 |
| Fotografer & Videografer | DP            | Rp 1.500.000 |
| Makeup Artist            | Payment 1     | Rp 1.200.000 |
| Sewa Gedung Resepsi      | DP            | Rp 5.000.000 |
| Sound System & Lighting  | Payment 1     | Rp 1.800.000 |
| Wedding Organizer        | Payment 2     | Rp 3.000.000 |
| Undangan Pernikahan      | Final Payment | Rp 800.000   |
| Bunga & Rangkaian        | DP            | Rp 1.500.000 |
| Entertainment            | Payment 1     | Rp 2.200.000 |

**Wedding Total**: ~Rp 27.500.000

### 2. Operasional Expenses (10 items)

| Keperluan             | Event                 | Amount       |
| --------------------- | --------------------- | ------------ |
| Sewa Kantor           | Operasional Bulanan   | Rp 4.500.000 |
| Listrik & Air         | Utility Kantor        | Rp 850.000   |
| Internet & Telepon    | Komunikasi            | Rp 650.000   |
| Supplies Kantor       | ATK & Perlengkapan    | Rp 750.000   |
| Maintenance Kendaraan | Perawatan Operasional | Rp 1.200.000 |
| Cleaning Service      | Kebersihan Kantor     | Rp 800.000   |
| Security Service      | Keamanan Kantor       | Rp 1.100.000 |
| Software License      | Lisensi Aplikasi      | Rp 2.300.000 |
| Training Karyawan     | Pengembangan SDM      | Rp 1.800.000 |
| Marketing & Promosi   | Advertising           | Rp 2.500.000 |

**Operasional Total**: ~Rp 16.450.000

### 3. Lain-lain Expenses (10 items)

| Keperluan          | Event               | Amount       |
| ------------------ | ------------------- | ------------ |
| CSR Komunitas      | Program Sosial      | Rp 1.500.000 |
| Team Building      | Gathering Karyawan  | Rp 2.200.000 |
| Hadiah Client      | Apresiasi Pelanggan | Rp 800.000   |
| Donasi Amal        | Kegiatan Sosial     | Rp 1.200.000 |
| Penelitian Pasar   | Market Research     | Rp 1.800.000 |
| Konsultasi Hukum   | Legal Advisory      | Rp 2.500.000 |
| Audit Keuangan     | Financial Audit     | Rp 3.200.000 |
| Asuransi Bisnis    | Business Insurance  | Rp 1.800.000 |
| Seminar & Workshop | Knowledge Sharing   | Rp 1.500.000 |
| Emergency Fund     | Dana Darurat        | Rp 2.000.000 |

**Lain-lain Total**: ~Rp 18.500.000

## 🔧 Technical Details

### Invoice Numbering System

-   **Wedding**: INV-W-001, INV-W-002, etc.
-   **Operasional**: INV-O-001, INV-O-002, etc.
-   **Lain-lain**: INV-L-001, INV-L-002, etc.

### Status Distribution

-   `belum_dibayar` (Belum Dibayar)
-   `menunggu` (Menunggu Pembayaran)
-   `sudah_dibayar` (Sudah Dibayar)
    _Random distribution untuk realistic data_

### Data Relationships

-   **Vendor**: Random selection from existing vendors
-   **NotaDinas**: Random assignment to existing nota dinas
-   **Order**: For wedding expenses only, linked to processing orders
-   **Created Dates**: Random between 30 days ago and now

## 🚀 Usage

### Run the Seeder

```bash
php artisan db:seed --class=NotaDinasDetailSeeder
```

### Prerequisites

Ensure these seeders have been run first:

1. `VendorSeeder` - for vendor data
2. `NotaDinasSeeder` - for nota dinas records
3. `OrderSeeder` - for wedding order assignments

## 📊 Expected Output

```
Successfully created 30 NotaDinasDetail records:
- Wedding: 10 records
- Operasional: 10 records
- Lain-lain: 10 records
Total: 30 records

+-------------------+---------------+----------------+
| Jenis Pengeluaran | Total Records | Total Amount   |
+-------------------+---------------+----------------+
| Wedding           | 10            | Rp 27.500.000  |
| Operasional       | 10            | Rp 16.450.000  |
| Lain-lain         | 10            | Rp 18.500.000  |
| TOTAL             | 30            | Rp 62.450.000  |
+-------------------+---------------+----------------+
```

## 🎨 Features Tested

### Wedding Expenses

-   ✅ Payment stages (DP, Payment 1, Payment 2, Final Payment)
-   ✅ Order relationship linking
-   ✅ Realistic wedding vendor needs

### Operasional Expenses

-   ✅ Monthly operational costs
-   ✅ Various business operational categories
-   ✅ Event field usage

### Lain-lain Expenses

-   ✅ Miscellaneous business expenses
-   ✅ CSR and social activities
-   ✅ Professional services

### General Features

-   ✅ Bank information auto-population from vendor
-   ✅ Invoice numbering system
-   ✅ Status randomization
-   ✅ Date range distribution
-   ✅ Error handling and reporting

## 🔍 Verification

After running the seeder, you can verify the data:

```sql
-- Check distribution by expense type
SELECT jenis_pengeluaran, COUNT(*), SUM(jumlah_transfer)
FROM nota_dinas_details
GROUP BY jenis_pengeluaran;

-- Check payment stages for wedding
SELECT payment_stage, COUNT(*)
FROM nota_dinas_details
WHERE jenis_pengeluaran = 'wedding'
GROUP BY payment_stage;

-- Check invoice status distribution
SELECT status_invoice, COUNT(*)
FROM nota_dinas_details
GROUP BY status_invoice;
```

This seeder provides a comprehensive test dataset for the NotaDinasDetail system with realistic expense data across all categories.
