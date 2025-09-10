# Pengurangan (Deductions) Feature Implementation

## Overview

Successfully added the "pengurangan" (deductions) field to the payroll system, allowing for tracking of salary deductions such as BPJS, lateness penalties, and other deductions.

## Changes Made

### 1. Database Structure

**Migration:** `2025_09_10_185545_add_pengurangan_to_payrolls_table.php`

-   Added `pengurangan` column (decimal 15,2, nullable)
-   Positioned after `tunjangan` column

### 2. Model Updates (app/Models/Payroll.php)

-   Added `pengurangan` to `$fillable` array
-   Added `pengurangan` to `$casts` as 'decimal:2'
-   Updated calculation logic in `boot()` method:
    ```php
    monthly_salary = gaji_pokok + tunjangan - pengurangan
    ```

### 3. PayrollResource Form Enhancement

**New Formula:** `Gaji Pokok + Tunjangan - Pengurangan = Total Gaji Bulanan`

**Form Layout:** Changed from 3-column to 4-column grid:

-   **Gaji Pokok**: Base salary input
-   **Tunjangan**: Allowances input
-   **Pengurangan**: Deductions input with placeholder "BPJS, keterlambatan dan lainnya"
-   **Total Gaji Bulanan**: Auto-calculated result (read-only)

**Live Calculation:** All three input fields trigger real-time recalculation of:

-   Monthly salary
-   Annual salary
-   Total compensation

### 4. Table View Updates

-   Added `pengurangan` column (hidden by default, toggleable)
-   Updated monthly salary description to show: "Rp X + Rp Y - Rp Z"
-   Color-coded: danger (red) for pengurangan column

### 5. Salary Slip Template Updates

**Before:** Fixed 2% calculation for deductions
**After:** Uses actual `$record->pengurangan` from database

**Template Logic:**

```php
// Only show pengurangan row if there are actual deductions
@if($record->pengurangan && $record->pengurangan > 0)
<tr>
    <td>Pengurangan (BPJS & Lainnya)</td>
    <td>- Rp {{ number_format($record->pengurangan, 0, ',', '.') }}</td>
</tr>
@endif

// Total diterima = monthly_salary (already includes deductions) + bonus
$totalDiterima = $record->monthly_salary + ($record->bonus ?? 0);
```

## Sample Data Results

Updated existing payroll records with realistic pengurangan amounts:

| Name             | Gaji Pokok   | Tunjangan    | Pengurangan | Total Gaji   |
| ---------------- | ------------ | ------------ | ----------- | ------------ |
| Rama Dhona Utama | Rp 4.000.000 | Rp 1.000.000 | Rp 100.000  | Rp 4.900.000 |
| Hera Ratna       | Rp 3.500.000 | Rp 1.500.000 | Rp 150.000  | Rp 4.850.000 |
| Rina Mardiana    | Rp 2.000.000 | Rp 1.000.000 | Rp 200.000  | Rp 2.800.000 |
| Adelia           | Rp 3.000.000 | Rp 800.000   | Rp 80.000   | Rp 3.720.000 |
| Qoyyum           | Rp 4.500.000 | Rp 1.200.000 | Rp 120.000  | Rp 5.580.000 |

## Testing & Verification

-   ✅ All calculations verified with test command
-   ✅ Form live updates work correctly
-   ✅ Database constraints maintained
-   ✅ Slip template displays accurate data
-   ✅ Table view shows proper breakdown

## Benefits

1. **Accurate Payroll Management**: Real tracking of all deductions
2. **Flexible Deduction Types**: BPJS, taxes, penalties, advances, etc.
3. **Transparent Calculations**: Employees see exact deduction amounts
4. **Compliance Ready**: Proper documentation for labor regulations
5. **Live Form Updates**: Immediate feedback on salary changes

## Usage Examples

### Common Deduction Types:

-   **BPJS Kesehatan**: 1% of salary
-   **BPJS Ketenagakerjaan**: 2% of salary
-   **Pajak Penghasilan**: Progressive based on salary
-   **Keterlambatan**: Penalty per incident
-   **Kasbon/Advance**: Employee cash advance deductions
-   **Denda**: Other penalty deductions

### Formula Verification:

```
Example: Employee with Rp 5,000,000 gaji pokok
+ Tunjangan: Rp 1,500,000
- Pengurangan: Rp 200,000 (BPJS + other)
= Total Gaji: Rp 6,300,000
```

## Files Modified

-   ✅ `database/migrations/2025_09_10_185545_add_pengurangan_to_payrolls_table.php`
-   ✅ `app/Models/Payroll.php`
-   ✅ `app/Filament/Resources/PayrollResource.php`
-   ✅ `resources/views/payroll/slip-gaji-download.blade.php`
-   ✅ Created test commands for verification

The pengurangan feature is now fully integrated and ready for production use.
