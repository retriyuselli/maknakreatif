# Cara Bank Transactions Mendapatkan Data Keterangan, Debit, dan Credit

## Gambaran Umum

Bank Transactions mendapatkan data dari file bank statement yang diimpor melalui system. Ada 2 jenis file yang dapat diproses:

### 1. **Balance History** (Yang saat ini ada di sistem)

-   **Konten**: Hanya berisi saldo akhir hari per tanggal
-   **Data**: `debit_amount = 0`, `credit_amount = 0`, hanya ada `balance`
-   **Deskripsi**: "Saldo Akhir Hari - CurrentAccount 1120077444474"
-   **Jumlah**: 30 record (semua balance history)

### 2. **Transaction History** (Yang diperlukan untuk detail transaksi)

-   **Konten**: Detail setiap transaksi dengan debit/credit
-   **Data**: `debit_amount > 0` atau `credit_amount > 0`
-   **Deskripsi**: Detail transaksi sebenarnya (transfer, pembayaran, dll)
-   **Jumlah**: Bisa ratusan transaksi per periode

## Alur Data Bank Transactions

```
File Bank Statement → BankStatementImport → BankTransaction Model → ReconciliationComparisonService → View
```

### Step 1: Upload File Bank Statement

```php
// Di PaymentMethodResource
Action::make('rekonsiliasi_bank')
    ->form([
        FileUpload::make('bank_statement')
            ->acceptedFileTypes(['application/pdf', 'text/csv', '.xlsx'])
    ])
```

### Step 2: Import Processing

```php
// Di BankStatementImport.php
public function collection(Collection $rows)
{
    $bankFormat = $this->detectBankFormat($rows);

    if ($bankFormat === 'mandiri_balance_history') {
        $this->processMandiriBalanceHistory($rows);      // ✅ Saat ini
    } elseif ($bankFormat === 'mandiri_transaction_history') {
        $this->processMandiriTransactionHistory($rows);  // ✅ Baru ditambahkan
    }
}
```

### Step 3: Data Mapping

```php
// Transaction History menghasilkan:
BankTransaction::create([
    'transaction_date' => '2025-09-15',
    'description' => 'Transfer ke PT ABC',           // ✅ Keterangan detail
    'debit_amount' => 5000000,                       // ✅ Debit amount
    'credit_amount' => 0,                            // ✅ Credit amount
    'balance' => 125000000,
    'transaction_type' => 'debit'
]);
```

### Step 4: Service Processing

```php
// ReconciliationComparisonService.php
->map(function ($transaction) {
    return (object) [
        'description' => $transaction->description,   // Dari file bank
        'debit' => $transaction->debit_amount,       // Dari kolom debit file
        'credit' => $transaction->credit_amount,     // Dari kolom credit file
    ];
});
```

## Format File Bank Yang Didukung

### Format 1: Mandiri Balance History

```
Date          | Account Type | Currency | Account        | Available Balance | Hold Amount | Current Balance
2025-09-01    | CurrentAccount| IDR     | 1120077444474  | 355,097,825.72   | 0.00       | 355,097,825.72
2025-09-02    | CurrentAccount| IDR     | 1120077444474  | 340,500,200.00   | 0.00       | 340,500,200.00
```

**Result**: Hanya saldo, tidak ada detail transaksi

### Format 2: Mandiri Transaction History

```
Transaction Date | Description          | Reference    | Debit Amount | Credit Amount | Balance
2025-09-01      | Transfer dari XYZ    | TRF001       | 0.00         | 15,000,000    | 355,097,825
2025-09-01      | Bayar listrik PLN    | PLN001       | 500,000      | 0.00          | 354,597,825
2025-09-02      | Transfer ke supplier | TRF002       | 14,597,625   | 0.00          | 340,000,200
```

**Result**: Detail setiap transaksi dengan debit/credit

## Cara Mendapatkan Data Detail Transaksi

### 1. **Download File Yang Benar dari Bank**

-   Login ke internet banking Bank Mandiri
-   Pilih menu **"Transaction History"** bukan "Balance History"
-   Set periode yang diinginkan
-   Download dalam format Excel/CSV

### 2. **Upload File Transaction History**

-   Ke menu Payment Methods
-   Pilih rekening yang sesuai
-   Klik "Rekonsiliasi Bank"
-   Upload file Transaction History

### 3. **Sistem Otomatis Detect Format**

```php
private function detectBankFormat(Collection $rows): string
{
    $firstFewRows = $rows->take(15)->map(function($row) {
        return $row->implode(' ');
    })->implode(' ');

    if (stripos($firstFewRows, 'Balance History') !== false) {
        return 'mandiri_balance_history';     // ❌ Hanya saldo
    } elseif (stripos($firstFewRows, 'Transaction History') !== false) {
        return 'mandiri_transaction_history'; // ✅ Detail transaksi
    }
}
```

### 4. **Hasil di Comparison View**

Setelah upload Transaction History yang benar:

**System Transactions (20)**:

-   Date: 2025-09-10, Description: "DP MUA Pengantin", Debit: 1,000,000, Credit: 0

**Bank Transactions (150+)**:

-   Date: 2025-09-10, Description: "Transfer ke PT MUA", Debit: 1,000,000, Credit: 0
-   Date: 2025-09-11, Description: "Penerimaan dari client", Debit: 0, Credit: 5,000,000
-   Date: 2025-09-12, Description: "Bayar gaji karyawan", Debit: 3,500,000, Credit: 0

## Troubleshooting

### Q: Mengapa Bank Transactions hanya menampilkan "Saldo Akhir Hari"?

**A**: File yang diupload adalah Balance History, bukan Transaction History.

### Q: Bagaimana cara mendapatkan Transaction History?

**A**:

1. Login ke internet banking
2. Pilih menu "Account Statement" atau "Transaction History"
3. Jangan pilih "Balance History" atau "Balance Inquiry"
4. Download dalam format yang detail

### Q: Format file apa yang didukung?

**A**:

-   Excel (.xlsx)
-   CSV (.csv)
-   PDF (akan di-parse menggunakan OCR/text extraction)

### Q: Bank selain Mandiri?

**A**: System sudah siap untuk BCA, BNI, dan format generic. Tinggal upload file dengan format yang sesuai.

## Summary

**Saat ini**: Bank Transactions = Balance History (30 records, semua saldo = 0)
**Yang dibutuhkan**: Bank Transactions = Transaction History (ratusan records dengan debit/credit detail)
**Solusi**: Upload file Transaction History dari internet banking, bukan Balance History
