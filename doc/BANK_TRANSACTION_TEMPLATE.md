# Template Bank Transaction History

## 📋 **File Template Tersedia**

### 1. **Excel Template (Recommended)**

**File**: `storage/templates/Bank_Transaction_History_Template.xlsx`

-   ✅ Format Excel dengan styling
-   ✅ 2 sheets: Data + Instructions
-   ✅ 18 contoh transaksi
-   ✅ Format number otomatis
-   ✅ Validasi column width

### 2. **CSV Template (Simple)**

**File**: `storage/templates/bank_transaction_history_template.csv`

-   ✅ Format CSV sederhana
-   ✅ 15 contoh transaksi
-   ✅ Import langsung ke sistem

## 📊 **Struktur Template**

### Kolom Wajib:

| Column               | Format     | Example                  | Keterangan                    |
| -------------------- | ---------- | ------------------------ | ----------------------------- |
| **Transaction Date** | YYYY-MM-DD | 2025-10-15               | Tanggal transaksi             |
| **Description**      | Text       | Transfer ke Supplier ABC | Keterangan detail             |
| **Debit**            | Number     | 5000000                  | Pengeluaran (tanpa separator) |
| **Credit**           | Number     | 3000000                  | Penerimaan (tanpa separator)  |

### Rules:

-   ✅ **Satu transaksi = satu baris**
-   ✅ **Debit OR Credit**, jangan keduanya
-   ✅ **Date format**: YYYY-MM-DD
-   ✅ **Numbers**: Tanpa titik/koma separator
-   ❌ **Jangan kosongkan Description**

## 💼 **Contoh Data Template**

```excel
Transaction Date | Description                                    | Debit     | Credit
2025-10-01      | Saldo Awal Bulan Oktober                      | 0         | 450000000
2025-10-02      | Transfer dari PT Makna Kreatif - Invoice MK001| 0         | 15000000
2025-10-02      | Bayar Gaji Karyawan September                 | 25000000  | 0
2025-10-03      | Pembayaran Listrik PLN Kantor                | 850000    | 0
2025-10-04      | Transfer ke Supplier ABC - PO12345            | 12500000  | 0
2025-10-05      | Penerimaan dari Client PT DEF                | 0         | 35000000
2025-10-06      | Biaya Admin Bank Bulanan                     | 15000     | 0
2025-10-07      | Transfer Modal dari Investor                 | 0         | 100000000
```

## 🚀 **Cara Penggunaan**

### Step 1: Download Template

```bash
# Copy file dari storage/templates/
cp storage/templates/Bank_Transaction_History_Template.xlsx ~/Desktop/
```

### Step 2: Edit Template

1. **Buka file Excel**
2. **Hapus contoh data** (baris 2-19)
3. **Isi data transaksi Anda**:
    - Transaction Date: Format YYYY-MM-DD
    - Description: Keterangan yang jelas
    - Debit: Untuk pengeluaran
    - Credit: Untuk penerimaan
4. **Save file**

### Step 3: Upload ke Sistem

1. **Login admin** → Finance → Payment Methods
2. **Pilih rekening** yang sesuai
3. **Klik "Rekonsiliasi Bank"**
4. **Upload file Excel** yang sudah diisi
5. **Sistem otomatis import** dan detect format

### Step 4: Lihat Hasil

1. **Buka Comparison View**
2. **Pilih Payment Method + Date Range**
3. **Lihat Bank Transactions** dengan data detail

## 🎯 **Expected Results**

### Sebelum (Balance History):

```
Bank Transactions (30):
- 2025-09-01 | Saldo Akhir Hari | Debit: 0 | Credit: 0
- 2025-09-02 | Saldo Akhir Hari | Debit: 0 | Credit: 0
```

### Sesudah (Transaction History):

```
Bank Transactions (100+):
- 2025-10-02 | Transfer dari PT Makna Kreatif | Debit: 0 | Credit: 15,000,000
- 2025-10-02 | Bayar Gaji Karyawan | Debit: 25,000,000 | Credit: 0
- 2025-10-03 | Pembayaran Listrik PLN | Debit: 850,000 | Credit: 0
```

## 🔧 **Tips & Best Practices**

### Descriptions Yang Baik:

```
✅ "Transfer ke PT ABC - Invoice INV001"
✅ "Penerimaan dari Client XYZ - Project Website"
✅ "Bayar Gaji Karyawan Bulan September"
✅ "Pembayaran Listrik PLN Kantor Pusat"

❌ "Transfer"
❌ "Bayar"
❌ "Terima"
❌ "Admin"
```

### Format Angka:

```
✅ 15000000     (15 juta)
✅ 850000       (850 ribu)
✅ 25000000     (25 juta)

❌ 15,000,000   (jangan pakai koma)
❌ 15.000.000   (jangan pakai titik)
❌ Rp 15000000  (jangan pakai mata uang)
```

### Date Format:

```
✅ 2025-10-15
✅ 2025-01-01
✅ 2025-12-31

❌ 15/10/2025
❌ 15-Oct-2025
❌ October 15, 2025
```

## 📁 **File Locations**

-   **Excel Template**: `/storage/templates/Bank_Transaction_History_Template.xlsx`
-   **CSV Template**: `/storage/templates/bank_transaction_history_template.csv`
-   **Documentation**: `/doc/BANK_TRANSACTIONS_DATA_SOURCE.md`
-   **Import Logic**: `/app/Imports/BankStatementImport.php`

Template sudah siap digunakan! Download, edit sesuai transaksi Anda, dan upload ke sistem untuk melihat comparison view dengan data detail yang lengkap.
