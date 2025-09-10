# 🔧 Refactoring PayrollResource - Menggunakan Model Accessors

## ✅ **Status:** COMPLETED & TESTED

### 🎯 **Tujuan Refactoring:**

Memindahkan semua logic perhitungan dari PayrollResource ke Model Payroll menggunakan **Accessor Pattern** untuk:

-   **Single Responsibility:** Model bertanggung jawab untuk business logic
-   **Reusability:** Accessor dapat digunakan di seluruh aplikasi
-   **Maintainability:** Centralized calculation logic
-   **Consistency:** Perhitungan yang sama di semua tempat

---

## 🏗️ **Perubahan di Model Payroll.php:**

### **📋 Accessor yang Ditambahkan:**

#### **1. Calculation Accessors:**

```php
// Menghitung annual salary dari monthly salary
public function getCalculatedAnnualSalaryAttribute(): float
{
    return (float) ($this->monthly_salary ?? 0) * 12;
}

// Menghitung total kompensasi
public function getTotalCompensationAttribute(): float
{
    return $this->calculated_annual_salary + (float) ($this->bonus ?? 0);
}
```

#### **2. Formatting Accessors (Tanpa Prefix):**

```php
public function getFormattedMonthlySalaryAttribute(): string
{
    return number_format((float) $this->monthly_salary, 0, '.', '.');
}

public function getFormattedAnnualSalaryAttribute(): string
{
    return number_format($this->calculated_annual_salary, 0, '.', '.');
}

public function getFormattedBonusAttribute(): string
{
    return number_format((float) ($this->bonus ?? 0), 0, '.', '.');
}

public function getFormattedTotalCompensationAttribute(): string
{
    return number_format($this->total_compensation, 0, '.', '.');
}
```

#### **3. Formatting Accessors (Dengan Prefix Rp):**

```php
public function getFormattedMonthlySalaryWithPrefixAttribute(): string
{
    return 'Rp ' . $this->formatted_monthly_salary;
}

public function getFormattedAnnualSalaryWithPrefixAttribute(): string
{
    return 'Rp ' . $this->formatted_annual_salary;
}

public function getFormattedBonusWithPrefixAttribute(): string
{
    return 'Rp ' . $this->formatted_bonus;
}

public function getFormattedTotalCompensationWithPrefixAttribute(): string
{
    return 'Rp ' . $this->formatted_total_compensation;
}
```

#### **4. Boot Method:**

```php
protected static function boot()
{
    parent::boot();

    static::saving(function ($payroll) {
        // Otomatis hitung annual_salary setiap kali monthly_salary berubah
        if ($payroll->monthly_salary) {
            $payroll->annual_salary = $payroll->monthly_salary * 12;
        }
    });
}
```

---

## 🔄 **Perubahan di PayrollResource.php:**

### **Before (Manual Calculation):**

```php
// ❌ Manual calculation di resource
->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
    $monthly = $state ? (float) str_replace(['.', ','], '', $state) : 0;
    $annual = $monthly * 12;
    $bonus = $get('bonus') ? (float) str_replace(['.', ','], '', $get('bonus')) : 0;
    $total = $annual + $bonus;
    $set('annual_salary', number_format($annual, 0, '.', '.'));
    $set('total_compensation', number_format($total, 0, '.', '.'));
})
```

### **After (Using Model Accessors):**

```php
// ✅ Clean code using model accessors
->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state, $record) {
    // Buat instance sementara untuk menggunakan accessor
    $tempPayroll = new \App\Models\Payroll();
    $monthly = $state ? (float) str_replace(['.', ','], '', $state) : 0;
    $tempPayroll->monthly_salary = $monthly;
    $tempPayroll->bonus = $get('bonus') ? (float) str_replace(['.', ','], '', $get('bonus')) : 0;

    // Gunakan accessor untuk perhitungan
    $set('annual_salary', $tempPayroll->formatted_annual_salary);
    $set('total_compensation', $tempPayroll->formatted_total_compensation);
})
```

### **Edit Mode (afterStateHydrated):**

```php
// ✅ Using accessor for consistency
->afterStateHydrated(function (Forms\Components\TextInput $component, $state, $record) {
    if ($record) {
        // Edit mode: gunakan accessor dari model
        $component->state($record->formatted_total_compensation);
    }
})
```

---

## 🔄 **Perubahan di UserResource.php:**

### **Before:**

```php
// ❌ Manual formatting
return sprintf(
    "Gaji Tahunan: %s\nBonus: %s\nTotal: %s\nPeriode: %s",
    'Rp ' . number_format($latestPayroll->annual_salary, 0, '.', '.'),
    'Rp ' . number_format($latestPayroll->bonus, 0, '.', '.'),
    'Rp ' . number_format($latestPayroll->annual_salary + $latestPayroll->bonus, 0, '.', '.'),
    $latestPayroll->pay_period ?? 'N/A'
);
```

### **After:**

```php
// ✅ Clean code using accessor
return sprintf(
    "Gaji Tahunan: %s\nBonus: %s\nTotal: %s\nPeriode: %s",
    $latestPayroll->formatted_annual_salary_with_prefix,
    $latestPayroll->formatted_bonus_with_prefix,
    $latestPayroll->formatted_total_compensation_with_prefix,
    $latestPayroll->pay_period ?? 'N/A'
);
```

---

## 🔄 **Perubahan di View Templates:**

### **salary-history.blade.php Before:**

```blade
<!-- ❌ Manual formatting -->
<div class="text-lg font-bold text-blue-900 dark:text-blue-100">
    Rp {{ number_format($payroll->monthly_salary, 0, '.', '.') }}
</div>
```

### **salary-history.blade.php After:**

```blade
<!-- ✅ Using accessor -->
<div class="text-lg font-bold text-blue-900 dark:text-blue-100">
    {{ $payroll->formatted_monthly_salary_with_prefix }}
</div>
```

---

## ✅ **Testing Results:**

### **🧪 Test Case 1: New Instance**

```
Monthly: Rp 7.500.000
Annual: Rp 90.000.000 (7.5M × 12)
Bonus: Rp 2.000.000
Total: Rp 92.000.000 ✅
```

### **🔧 Test Case 2: Accessor Functionality**

```php
$payroll = new App\Models\Payroll();
$payroll->monthly_salary = 6000000;
$payroll->bonus = 1500000;

// Results:
$payroll->formatted_monthly_salary_with_prefix;     // Rp 6.000.000
$payroll->formatted_annual_salary_with_prefix;      // Rp 72.000.000
$payroll->formatted_bonus_with_prefix;              // Rp 1.500.000
$payroll->formatted_total_compensation_with_prefix; // Rp 73.500.000
```

---

## 🎯 **Benefits Achieved:**

### **✨ Code Quality:**

-   **DRY Principle:** No more duplicate calculation logic
-   **Single Source of Truth:** All calculations in model
-   **Cleaner Resource:** PayrollResource focuses on UI logic only
-   **Reusable Logic:** Accessors can be used anywhere

### **🛡️ Maintainability:**

-   **Centralized Logic:** Change calculation once, affects everywhere
-   **Consistent Results:** Same calculation across all features
-   **Easy Testing:** Test business logic in model unit tests
-   **Better Separation:** Clear separation between business & presentation logic

### **🚀 Performance:**

-   **Lazy Loading:** Accessors calculated only when needed
-   **Cached Results:** Laravel automatically caches accessor results
-   **Optimized Queries:** Better control over data retrieval

### **👥 Developer Experience:**

-   **Intellisense Support:** IDE can autocomplete accessor methods
-   **Self-Documenting:** Method names clearly show what they return
-   **Type Safety:** Return types ensure data consistency

---

## 🌐 **Usage Examples:**

### **In Controllers:**

```php
$payroll = Payroll::find(1);
return response()->json([
    'monthly' => $payroll->formatted_monthly_salary_with_prefix,
    'total' => $payroll->formatted_total_compensation_with_prefix
]);
```

### **In Blade Templates:**

```blade
<p>Gaji: {{ $payroll->formatted_monthly_salary_with_prefix }}</p>
<p>Total: {{ $payroll->formatted_total_compensation_with_prefix }}</p>
```

### **In API Resources:**

```php
return [
    'monthly_salary' => $this->formatted_monthly_salary,
    'total_compensation' => $this->formatted_total_compensation,
];
```

---

## 🎉 **Conclusion:**

✅ **All calculations now use Model Accessors**  
✅ **PayrollResource code is cleaner and more maintainable**  
✅ **UserResource tooltip uses consistent formatting**  
✅ **View templates use centralized formatting logic**  
✅ **Testing confirms all accessors work correctly**  
✅ **Future calculations will be consistent across the application**

**Refactoring berhasil! Semua perhitungan sekarang menggunakan accessor dari Model Payroll.php dan kode menjadi lebih clean, maintainable, dan reusable.** 🎉
