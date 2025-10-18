# Konsep Debit/Credit vs Masuk/Keluar dalam Bank Reconciliation

## Permasalahan

-   **Aplikasi**: Menggunakan konsep "Uang Masuk/Keluar" yang mudah dipahami user
-   **Bank**: Menggunakan konsep "Debit/Credit" standar perbankan
-   **User**: Bingung dengan perbedaan konsep ini

## Solusi Mapping

### 1. Konsep Bank (Standar Perbankan)

```
DEBIT  = Uang Keluar dari Rekening (Pengeluaran)
CREDIT = Uang Masuk ke Rekening   (Pemasukan)
```

### 2. Konsep Aplikasi (User-Friendly)

```
KELUAR = Pengeluaran/Pembayaran   (= Debit Bank)
MASUK  = Pemasukan/Penerimaan     (= Credit Bank)
```

### 3. Mapping Table

| Bank Statement | Aplikasi Internal | Warna Display | Icon |
| -------------- | ----------------- | ------------- | ---- |
| Debit (+)      | Uang Keluar (-)   | Merah         | ↗️   |
| Credit (-)     | Uang Masuk (+)    | Hijau         | ↙️   |

## Implementasi

### 1. Model Enhancement

-   Tambah method `getDirectionLabel()` untuk mapping user-friendly
-   Tambah `getAmountWithDirection()` untuk display yang konsisten

### 2. View Enhancement

-   Display "Masuk/Keluar" di user interface
-   Tetap simpan debit/credit di database
-   Tambah tooltip explanation

### 3. Form Enhancement

-   Input form menggunakan bahasa "Masuk/Keluar"
-   Auto-convert ke debit/credit saat save
-   Validation yang sesuai

## Benefit

-   User tidak perlu memahami konsep debit/credit
-   Database tetap standar perbankan
-   Reconciliation tetap akurat
-   Interface lebih user-friendly
