# 📊 Download Template Bank Transaction History

## 🎯 **Template Siap Pakai**

### ✅ **Excel Template (Recommended)**

**File**: `Bank_Transaction_History_Template.xlsx`
**Location**: `/storage/templates/Bank_Transaction_History_Template.xlsx`

**Features**:

-   📋 2 Sheets: Data + Instructions
-   💼 18 contoh transaksi realistic
-   🎨 Formatting & styling
-   📝 Instruksi lengkap
-   🔢 Number format otomatis

### ✅ **CSV Template (Simple)**

**File**: `bank_transaction_history_template.csv`  
**Location**: `/storage/templates/bank_transaction_history_template.csv`

**Features**:

-   📄 Format CSV sederhana
-   💼 15 contoh transaksi
-   ⚡ Quick import
-   🔧 Edit dengan text editor

## 📝 **Format Template**

### Kolom Wajib (4 kolom):

```
Transaction Date | Description                      | Debit     | Credit
2025-10-01      | Saldo Awal Bulan                | 0         | 450000000
2025-10-02      | Transfer dari Client ABC         | 0         | 15000000
2025-10-03      | Bayar Gaji Karyawan             | 25000000  | 0
2025-10-04      | Pembayaran Listrik PLN          | 850000    | 0
```

### Rules Penting:

-   ✅ **Date**: Format YYYY-MM-DD (2025-10-15)
-   ✅ **Description**: Keterangan yang jelas dan spesifik
-   ✅ **Debit**: Pengeluaran (angka tanpa separator)
-   ✅ **Credit**: Penerimaan (angka tanpa separator)
-   ❌ **Jangan isi Debit dan Credit bersamaan di satu baris**

## 🚀 **Quick Start Guide**

### 1. **Copy Template ke Desktop**

```bash
# Via file manager atau terminal
cp /path/to/project/storage/templates/Bank_Transaction_History_Template.xlsx ~/Desktop/
```

### 2. **Edit Template**

-   Buka Excel file
-   Hapus contoh data (row 2-19)
-   Isi dengan data transaksi bank Anda
-   Save file

### 3. **Upload ke Sistem**

-   Login admin → Finance → Payment Methods
-   Pilih rekening → Rekonsiliasi Bank
-   Upload file Excel yang sudah diisi
-   Sistem auto-detect format "Transaction History"

### 4. **Lihat Hasil**

-   Buka Comparison View
-   Pilih Payment Method + Date Range
-   Lihat Bank Transactions dengan data lengkap

## 📋 **Contoh Data Lengkap**

### Sample Transactions di Template:

```
2025-10-01 | Saldo Awal Bulan Oktober                      | 0         | 450000000
2025-10-02 | Transfer dari PT Makna Kreatif - Invoice MK001| 0         | 15000000
2025-10-02 | Bayar Gaji Karyawan September                 | 25000000  | 0
2025-10-03 | Pembayaran Listrik PLN Kantor                | 850000    | 0
2025-10-04 | Transfer ke Supplier ABC - PO12345            | 12500000  | 0
2025-10-05 | Penerimaan dari Client PT DEF                | 0         | 35000000
2025-10-06 | Biaya Admin Bank Bulanan                     | 15000     | 0
2025-10-07 | Transfer Modal dari Investor                 | 0         | 100000000
2025-10-08 | Bayar Sewa Kantor Oktober                    | 8000000   | 0
2025-10-09 | Pembelian Peralatan Kantor                   | 3500000   | 0
2025-10-10 | Penerimaan Fee Jasa Konsultasi               | 0         | 7500000
2025-10-11 | Transfer Pajak ke Bank Mandiri               | 5200000   | 0
2025-10-12 | Bunga Deposito 6 Bulan                       | 0         | 750000
2025-10-13 | Langganan Software Office 365               | 350000    | 0
2025-10-14 | Penjualan Online E-commerce                  | 0         | 28500000
2025-10-15 | Transfer ke Cabang Operasional               | 15000000  | 0
2025-10-16 | Invoice Project Website                      | 0         | 45000000
```

## ✅ **System Compatibility**

Template sudah **100% compatible** dengan sistem:

-   ✅ Format Detection: `mandiri_transaction_history`
-   ✅ Auto-import dengan processor khusus
-   ✅ Parse debit/credit amounts correctly
-   ✅ Extract descriptions dengan benar
-   ✅ Support date parsing YYYY-MM-DD
-   ✅ Validasi data dan error handling

## 🎯 **Expected Results**

Setelah upload template yang sudah diisi:

**Before** (Balance History):

-   Bank Transactions: 30 records
-   Semua debit/credit = 0
-   Description: "Saldo Akhir Hari"

**After** (Transaction History):

-   Bank Transactions: 100+ records (sesuai data Anda)
-   Debit/credit sesuai transaksi real
-   Description: Detail transaksi spesifik
-   Perfect matching untuk reconciliation

**🎉 Template siap download dan digunakan!**
