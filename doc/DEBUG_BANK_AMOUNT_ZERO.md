# Debug: Diagnostic untuk Bank Amount Zero Issue

## 🚨 Problem

Data Bank Statement menunjukkan:

-   MCM InhouseTrf DARI ADELIA TIARA SANDEI 99101
-   Jenis: Masuk (Credit)
-   Jumlah: 30.550.000

Tetapi di "Transaksi yang Cocok" nominal menjadi 0.

## 🔍 Diagnostic Steps

### 1. Check Database Raw Data

```sql
-- Check BankReconciliationItem untuk record tersebut
SELECT id, bank_reconciliation_id, date, description, debit, credit, row_number
FROM bank_reconciliation_items
WHERE description LIKE '%ADELIA TIARA SANDEI%'
OR description LIKE '%MCM InhouseTrf%';

-- Check BankStatement terkait
SELECT id, payment_method_id, period_start, period_end, tot_credit, tot_debit
FROM bank_statements
WHERE id = [bank_reconciliation_id];
```

### 2. Check Model Casting Issue

Kemungkinan issue:

-   Field `credit` ter-cast sebagai `decimal:2` tapi data asli dalam format berbeda
-   Data disimpan sebagai string yang tidak bisa di-cast ke number
-   Field mapping salah antara import dan display

### 3. Check Import Process

```php
// Dalam BankStatementImport.php
// Pastikan data 30,550,000 diparse dengan benar:
$creditAmount = $this->parseAmount($row['credit'] ?? $row['kredit'] ?? $row['masuk'] ?? 0);

// Method parseAmount harus handle:
// - 30,550,000 (dengan koma)
// - 30.550.000 (dengan titik)
// - 30550000 (tanpa separator)
```

### 4. Check Field Name Consistency

Kemungkinan field name berbeda:

```php
// BankReconciliationItem: debit, credit
// BankTransaction: debit_amount, credit_amount
// Pastikan menggunakan field yang tepat
```

## 🛠️ Immediate Fix Applied

Menambahkan debug info pada view untuk melihat:

```php
@if($hasZeroIssue)
    <div class="text-red-600 font-bold">🚨 DEBUG: Bank amount issue!</div>
    <div>Bank Debit: {{ $bankDebit }}</div>
    <div>Bank Credit: {{ $bankCredit }}</div>
    <div>Bank ID: {{ $bankItem->id ?? 'NULL' }}</div>
    <div>Raw Data: {{ json_encode($bankItem->toArray()) }}</div>
@endif
```

## 📋 Next Steps

1. **Refresh halaman** `http://127.0.0.1:8000/admin/bank-statements/65/reconciliation-alt`
2. **Lihat debug info** yang muncul untuk record ADELIA TIARA SANDEI
3. **Analisis raw data** untuk identify root cause
4. **Fix import/casting** berdasarkan temuan

## 🎯 Possible Root Causes

### A. Data Import Issue

```php
// Data 30,550,000 tidak ter-parse dengan benar
// Stored as: "30,550,000" (string) instead of 30550000 (number)
```

### B. Field Mapping Issue

```php
// Wrong field reference
$bankAmount = $bankItem->credit_amount; // ❌ Wrong field
$bankAmount = $bankItem->credit;        // ✅ Correct field
```

### C. Decimal Casting Issue

```php
// Model casting issue
'credit' => 'decimal:2' // Might fail on certain formats
```

### D. Database NULL/Empty Values

```php
// Database contains NULL or empty values instead of actual amounts
```

Dengan debug info yang sudah ditambahkan, kita akan dapat melihat data mentah dan mengidentifikasi masalah yang tepat!
