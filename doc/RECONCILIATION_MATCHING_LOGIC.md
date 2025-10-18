# Logic Penentuan Transaksi Cocok (Bank Reconciliation Matching)

## Overview

Sistem bank reconciliation menggunakan algoritma matching 2-fase untuk mencocokkan transaksi aplikasi dengan data bank statement.

---

## 🎯 Algoritma Matching

### **FASE 1: EXACT MATCH (100% Confidence)**

Mencari kecocokan sempurna berdasarkan:

#### Kriteria Exact Match:

1. **📅 Tanggal Sama Persis**
    - `app_transaction.transaction_date = bank_item.date`
2. **💰 Nominal Sama Persis**
    - **Untuk Income**: `app_transaction.credit_amount = bank_item.credit`
    - **Untuk Expense**: `app_transaction.debit_amount = bank_item.debit`
    - **Toleransi**: < 0.01 (untuk floating point precision)

#### Contoh Exact Match:

```
App Transaction:  2025-10-15 | Transfer dari Customer | Rp 5,000,000 (Credit)
Bank Statement:   2025-10-15 | Transfer masuk        | Rp 5,000,000 (Credit)
Result: ✅ MATCHED (100% Confidence)
```

---

### **FASE 2: FUZZY MATCH (50%-99% Confidence)**

Jika tidak ada exact match, cari kecocokan dengan scoring system:

#### 🗓️ **Date Proximity Scoring (Max: 40 poin)**

| Selisih Hari | Skor    | Kriteria     |
| ------------ | ------- | ------------ |
| 0 hari       | 40 poin | `same_date`  |
| 1 hari       | 30 poin | `close_date` |
| 2-3 hari     | 15 poin | `near_date`  |
| > 3 hari     | 0 poin  | ❌ Skip      |

#### 💵 **Amount Matching (Max: 40 poin)**

| Selisih Nominal      | Skor    | Kriteria       |
| -------------------- | ------- | -------------- |
| Sama persis (< 0.01) | 40 poin | `exact_amount` |
| Dalam 2%             | 25 poin | `close_amount` |
| > 2%                 | 0 poin  | ❌ Skip        |

#### 📝 **Description Similarity (Max: 20 poin)**

Menggunakan **Levenshtein Distance Algorithm**:

| Similarity Score | Skor    | Kriteria                 |
| ---------------- | ------- | ------------------------ |
| > 80%            | 20 poin | `high_desc_similarity`   |
| 60-80%           | 10 poin | `medium_desc_similarity` |
| 40-60%           | 5 poin  | `low_desc_similarity`    |
| < 40%            | 0 poin  | -                        |

#### **Formula Similarity:**

```php
similarity = 1 - (levenshtein_distance / max_length)
```

---

## 📊 **Confidence Level Classification**

| Confidence Score | Level        | Keterangan                  |
| ---------------- | ------------ | --------------------------- |
| 100%             | **EXACT**    | Match sempurna              |
| 85-99%           | **HIGH**     | Sangat yakin cocok          |
| 70-84%           | **MEDIUM**   | Kemungkinan besar cocok     |
| 50-69%           | **LOW**      | Mungkin cocok, perlu review |
| < 50%            | **NO MATCH** | Tidak cocok                 |

---

## 🔍 **Contoh Skenario Matching**

### Skenario 1: Exact Match

```
App: 2025-10-15 | "Bayar supplier ABC" | Rp 2,500,000 (Debit)
Bank: 2025-10-15 | "Transfer keluar" | Rp 2,500,000 (Debit)
Score: 40 (date) + 40 (amount) + 20 (desc low) = 100% ✅
```

### Skenario 2: High Confidence

```
App: 2025-10-15 | "Transfer dari PT XYZ" | Rp 1,000,000 (Credit)
Bank: 2025-10-16 | "Transfer masuk PT XYZ" | Rp 1,000,000 (Credit)
Score: 30 (1 day) + 40 (exact amount) + 20 (high desc) = 90% ✅
```

### Skenario 3: Medium Confidence

```
App: 2025-10-15 | "Pembayaran listrik" | Rp 500,000 (Debit)
Bank: 2025-10-17 | "PLN bayar tagihan" | Rp 510,000 (Debit)
Score: 15 (2 days) + 25 (close amount 2%) + 10 (medium desc) = 50% ⚠️
```

### Skenario 4: No Match

```
App: 2025-10-15 | "Gaji karyawan" | Rp 10,000,000 (Debit)
Bank: 2025-10-20 | "Transfer keluar" | Rp 5,000,000 (Debit)
Score: 0 (>3 days) = Skip ❌
```

---

## ⚙️ **Parameter Konfigurasi**

### Confidence Thresholds:

```php
const EXACT_MATCH_CONFIDENCE = 100.00;
const HIGH_CONFIDENCE = 85.00;
const MEDIUM_CONFIDENCE = 70.00;
const LOW_CONFIDENCE = 50.00;     // Minimum untuk dianggap match
```

### Toleransi:

-   **Date Range**: Max 3 hari
-   **Amount Tolerance**: Max 2% dari nominal
-   **Minimum Confidence**: 50% untuk dianggap match

---

## 🎯 **Kriteria Matching Berdasarkan Jenis Transaksi**

### **Income Transactions (Pemasukan)**

```php
// App transaction dengan credit_amount > 0
// Harus match dengan bank item credit > 0
$amountMatch = abs($appTx->credit_amount - $bankItem->credit) < 0.01;
```

### **Expense Transactions (Pengeluaran)**

```php
// App transaction dengan debit_amount > 0
// Harus match dengan bank item debit > 0
$amountMatch = abs($appTx->debit_amount - $bankItem->debit) < 0.01;
```

---

## 📋 **Output Hasil Reconciliation**

### **Matched Transactions:**

```php
[
    'app_transaction' => $appTx,
    'bank_item' => $bankItem,
    'confidence' => 85.5,
    'match_type' => 'fuzzy',
    'match_criteria' => ['close_date', 'exact_amount', 'medium_desc_similarity']
]
```

### **Statistics:**

```php
[
    'total_app_transactions' => 150,
    'total_bank_items' => 145,
    'matched_count' => 120,
    'unmatched_app_count' => 30,
    'unmatched_bank_count' => 25,
    'match_percentage' => 80.0
]
```

---

## 🚀 **Optimization Tips**

1. **Improve Description Quality**: Standarisasi format keterangan
2. **Consistent Timing**: Input transaksi sesuai tanggal sebenarnya
3. **Amount Precision**: Hindari pembulatan yang tidak perlu
4. **Regular Review**: Review manual untuk low confidence matches

---

## 🔧 **Manual Override**

Sistem juga menyediakan fitur **Manual Match** untuk:

-   Transaksi dengan confidence < 50%
-   Force matching untuk kasus khusus
-   Override automatic matching jika diperlukan
