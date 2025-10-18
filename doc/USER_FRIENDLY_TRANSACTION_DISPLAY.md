# User-Friendly Bank Transaction Helper

## Konsep Mapping

Aplikasi ini menggunakan konsep user-friendly dengan tetap menjaga standar perbankan di backend:

### Display untuk User:

-   **Uang Masuk** = Penerimaan, Dana Masuk ke Rekening
-   **Uang Keluar** = Pengeluaran, Dana Keluar dari Rekening

### Mapping ke Bank Standard:

-   **Uang Masuk** → Credit (Bank terminology)
-   **Uang Keluar** → Debit (Bank terminology)

## Implementation Guide

### 1. Form Input

```php
// User melihat: "Uang Masuk/Keluar"
// System converts to: debit/credit
```

### 2. Table Display

```php
// Primary: "Masuk/Keluar" dengan warna
// Secondary: "Debit/Credit" (optional, dapat di-hide)
```

### 3. Tooltips

-   Hover pada amount menunjukkan bank terminology
-   Tooltips menjelaskan mapping debit/credit

### 4. Colors & Icons

-   🟢 **Hijau** + **↙️** = Uang Masuk (Credit)
-   🔴 **Merah** + **↗️** = Uang Keluar (Debit)

## Benefits

1. **User Experience**: Lebih mudah dipahami
2. **Technical Accuracy**: Database tetap standar
3. **Reconciliation**: Tetap akurat
4. **Training**: Mengurangi kebingungan user
