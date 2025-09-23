# 📊 DOKUMENTASI ANALISA NERACA LAPORAN KEUANGAN

## 🎯 **TUJUAN**

Membuat Laporan Neraca (Balance Sheet) tahunan berdasarkan data sistem yang sudah ada di aplikasi Makna Kreatif.

---

## 📋 **ANALISA DATA EXISTING**

### ✅ **DATA YANG SUDAH TERSEDIA:**

#### **ASET (Assets):**

1. **💰 Kas & Bank**

    - **Sumber**: Tabel `payment_methods`
    - **Status**: ✅ Ready
    - **Implementasi**: Tracking saldo per bank account

2. **📋 Piutang Usaha**
    - **Sumber**: Tabel `piutangs` kolom `sisa_piutang`
    - **Status**: ✅ Ready
    - **Implementasi**: Sum total piutang yang belum dibayar

#### **KEWAJIBAN (Liabilities):**

1. **💳 Hutang Usaha (ke Vendor)**

    - **Sumber**: Tabel `expenses`
    - **Status**: ⚠️ Partial
    - **Implementasi**: Expenses yang belum dibayar

2. **👥 Hutang Gaji**
    - **Sumber**: Tabel `leave_requests` (tracking karyawan)
    - **Status**: ❌ Missing
    - **Kebutuhan**: Sistem payroll

#### **EKUITAS (Equity):**

1. **🏦 Modal**

    - **Status**: ❌ Missing
    - **Kebutuhan**: Tabel khusus modal awal

2. **📈 Laba Ditahan**
    - **Sumber**: Kalkulasi dari income vs expenses
    - **Status**: ⚠️ Calculable
    - **Implementasi**: Agregasi data existing

---

## 🛠️ **DATA YANG PERLU DITAMBAHKAN**

### **1. ASET TETAP (Fixed Assets):**

```sql
CREATE TABLE aset_tetaps (
    id BIGINT PRIMARY KEY,
    nama_aset VARCHAR(255),
    kategori ENUM('gedung', 'kendaraan', 'peralatan', 'furniture'),
    harga_perolehan DECIMAL(15,2),
    tanggal_perolehan DATE,
    umur_ekonomis INT, -- dalam tahun
    nilai_sisa DECIMAL(15,2),
    akumulasi_penyusutan DECIMAL(15,2) DEFAULT 0,
    nilai_buku DECIMAL(15,2),
    status ENUM('aktif', 'dijual', 'rusak'),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### **2. PERSEDIAAN (Inventory):**

```sql
CREATE TABLE persediaans (
    id BIGINT PRIMARY KEY,
    nama_item VARCHAR(255),
    kategori VARCHAR(100),
    qty INT,
    harga_satuan DECIMAL(15,2),
    total_nilai DECIMAL(15,2),
    tanggal_masuk DATE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### **3. MODAL & EKUITAS:**

```sql
CREATE TABLE modals (
    id BIGINT PRIMARY KEY,
    jenis_modal ENUM('modal_awal', 'modal_tambahan', 'laba_ditahan'),
    jumlah DECIMAL(15,2),
    tanggal_setoran DATE,
    keterangan TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### **4. SALDO BANK:**

```sql
-- Tambahan kolom ke payment_methods
ALTER TABLE payment_methods ADD COLUMN saldo_awal DECIMAL(15,2) DEFAULT 0;
ALTER TABLE payment_methods ADD COLUMN saldo_current DECIMAL(15,2) DEFAULT 0;
```

### **5. CHART OF ACCOUNTS (Opsional untuk sistem lengkap):**

```sql
CREATE TABLE akun_saldos (
    id BIGINT PRIMARY KEY,
    kode_akun VARCHAR(20) UNIQUE,
    nama_akun VARCHAR(255),
    kategori ENUM('aset', 'kewajiban', 'ekuitas', 'pendapatan', 'beban'),
    tipe ENUM('debit', 'kredit'),
    saldo_debit DECIMAL(15,2) DEFAULT 0,
    saldo_kredit DECIMAL(15,2) DEFAULT 0,
    parent_id BIGINT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 📈 **TEMPLATE NERACA YANG BISA DIBUAT**

### **FASE 1 - NERACA SIMPLIFIED:**

```
MAKNA KREATIF
NERACA (BALANCE SHEET)
Per 31 Desember 2025

ASET:
├── Aset Lancar:
│   ├── Kas & Bank               : Rp XXX
│   └── Piutang Usaha           : Rp XXX
├── Total Aset Lancar           : Rp XXX
└── TOTAL ASET                  : Rp XXX

KEWAJIBAN:
├── Kewajiban Lancar:
│   └── Hutang Usaha            : Rp XXX
├── Total Kewajiban Lancar      : Rp XXX
└── TOTAL KEWAJIBAN             : Rp XXX

EKUITAS:
├── Laba Berjalan               : Rp XXX
└── TOTAL EKUITAS               : Rp XXX

TOTAL KEWAJIBAN + EKUITAS       : Rp XXX
```

### **FASE 2 - NERACA COMPLETE:**

```
ASET:
├── Aset Lancar:
│   ├── Kas & Bank               : Rp XXX
│   ├── Piutang Usaha           : Rp XXX
│   └── Persediaan              : Rp XXX
├── Aset Tetap:
│   ├── Peralatan               : Rp XXX
│   ├── Akm. Penyusutan Peralatan: (Rp XXX)
│   └── Peralatan (Neto)        : Rp XXX
└── TOTAL ASET                  : Rp XXX

KEWAJIBAN:
├── Kewajiban Lancar:
│   ├── Hutang Usaha            : Rp XXX
│   └── Hutang Gaji             : Rp XXX
├── Kewajiban Jangka Panjang:
│   └── Hutang Bank             : Rp XXX
└── TOTAL KEWAJIBAN             : Rp XXX

EKUITAS:
├── Modal Disetor               : Rp XXX
├── Laba Ditahan                : Rp XXX
├── Laba Tahun Berjalan         : Rp XXX
└── TOTAL EKUITAS               : Rp XXX
```

---

## 🚀 **RENCANA IMPLEMENTASI**

### **FASE 1 - Quick Win (1-2 Hari):**

1. **✅ Widget Neraca Dashboard** - Berdasarkan data existing
2. **✅ Laporan Basic** - Kas, Piutang, Hutang, Laba
3. **✅ Filter per Tahun** - Untuk perbandingan tahunan
4. **✅ Export to PDF/Excel** - Untuk keperluan eksternal

### **FASE 2 - Complete System (1-2 Minggu):**

1. **🛠️ Tambah Tabel Missing** - Aset tetap, Modal, Persediaan
2. **🛠️ Proper Accounting** - Chart of accounts, double entry
3. **🛠️ Depreciation System** - Auto-calculate penyusutan aset tetap
4. **🛠️ Advanced Reports** - dengan drill-down capability
5. **🛠️ Comparative Reports** - Perbandingan multi-tahun

---

## ❓ **PERTANYAAN YANG PERLU DIJAWAB BESOK**

### **Business Questions:**

1. **Scope Implementasi**: Mulai dengan Neraca sederhana atau langsung complete?
2. **Aset Tetap**: Apakah ada aset tetap yang perlu ditrack (gedung, kendaraan, equipment)?
3. **Modal Awal**: Berapa modal awal perusahaan dan struktur kepemilikan?
4. **Inventory**: Apakah bisnis wedding organizer ini ada inventory/stock barang?
5. **Bank Saldo**: Apakah perlu input saldo awal untuk setiap bank account?
6. **Periode**: Neraca untuk tahun berapa yang akan dibuat pertama kali?

### **Technical Questions:**

1. **Format Output**: PDF, Excel, atau dashboard widget?
2. **User Access**: Siapa saja yang bisa akses laporan neraca?
3. **Update Frequency**: Real-time, daily, atau manual trigger?
4. **Historical Data**: Perlu import data tahun-tahun sebelumnya?

---

## 💡 **KESIMPULAN**

**✅ FEASIBLE**: Dengan data existing, Neraca versi simplified **BISA** dibuat dengan struktur:

-   **Aset**: Kas + Piutang
-   **Kewajiban**: Hutang Usaha
-   **Ekuitas**: Laba Berjalan

**🎯 NEXT STEPS**:

1. Tentukan scope dan prioritas
2. Jawab pertanyaan business requirements
3. Mulai implementasi berdasarkan keputusan

---

## 📊 **DATA EXISTING YANG SUDAH TERANALISA**

### **Tabel Existing yang Relevan:**

1. **`payment_methods`** - Untuk tracking kas & bank
2. **`piutangs`** - Untuk piutang usaha (✅ sudah ada)
3. **`pembayaran_piutangs`** - Untuk tracking pembayaran (✅ sudah ada)
4. **`expenses`** - Untuk kalkulasi hutang usaha dan beban
5. **`nota_dinas`** & **`nota_dinas_details`** - Untuk expense tracking
6. **`orders`** - Untuk revenue/pendapatan
7. **`vendors`** - Untuk hutang vendor

### **Models yang Sudah Siap:**

-   ✅ `Piutang.php` - dengan status dan perhitungan
-   ✅ `PembayaranPiutang.php` - untuk tracking pembayaran
-   ✅ `Expense.php` - untuk beban perusahaan
-   ✅ `Order.php` - untuk pendapatan
-   ✅ `PaymentMethod.php` - untuk kas & bank

---

**📅 Tanggal Dokumentasi**: 15 September 2025  
**👤 Prepared by**: GitHub Copilot  
**📁 Project**: Makna Kreatif - Financial Reporting System

---

Dokumentasi ini siap untuk referensi implementasi Neraca. Semua analisa data existing dan rencana implementasi telah terdokumentasi lengkap untuk memudahkan pengembangan sistem laporan keuangan.
