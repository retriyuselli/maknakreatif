# UserResource - Leave Request Integration Documentation

## 🏖️ Leave Request Columns in User Table

### Overview

UserResource telah ditingkatkan dengan kolom-kolom baru yang menampilkan informasi leave request untuk setiap user, memberikan insights tentang penggunaan cuti karyawan.

## 📊 New Columns Added

### 1. **Cuti Diambil (Total Leave Taken)**

```php
Tables\Columns\TextColumn::make('total_leave_taken')
    ->label('Cuti Diambil')
    ->getStateUsing(function ($record) {
        return $record->leaveRequests()
            ->where('status', 'approved')
            ->whereYear('start_date', date('Y'))
            ->sum('total_days');
    })
```

**Features:**

-   ✅ Menampilkan total hari cuti yang **telah disetujui** tahun ini
-   ✅ Format: "X hari"
-   ✅ Badge dengan color coding:
    -   🟢 **Green (success)**: 0-6 hari (Normal)
    -   🟡 **Yellow (warning)**: 7-12 hari (Moderat)
    -   🔴 **Red (danger)**: >12 hari (Melebihi jatah)
    -   ⚫ **Gray**: 0 hari (Belum pernah cuti)
-   ✅ Icon: `heroicon-o-calendar-days`
-   ✅ Sortable
-   ✅ **Tooltip detail** menampilkan breakdown per status:
    ```
    Tahun 2025:
    Disetujui: 10 hari
    Menunggu: 4 hari
    Ditolak: 2 hari
    ```

### 2. **Sisa Cuti (Remaining Leave)**

```php
Tables\Columns\TextColumn::make('remaining_leave')
    ->label('Sisa Cuti')
    ->getStateUsing(function ($record) {
        $annualLeaveAllowance = 12; // Default 12 hari per tahun
        $usedLeave = $record->leaveRequests()
            ->where('status', 'approved')
            ->whereYear('start_date', date('Y'))
            ->sum('total_days');

        return max(0, $annualLeaveAllowance - $usedLeave);
    })
```

**Features:**

-   ✅ Menampilkan sisa jatah cuti tahun ini
-   ✅ Kalkulasi: **12 hari - cuti terpakai**
-   ✅ Format: "X hari"
-   ✅ Badge dengan color coding:
    -   🟢 **Green (success)**: ≥8 hari tersisa (Masih banyak)
    -   🟡 **Yellow (warning)**: 4-7 hari tersisa (Sedang)
    -   🔴 **Red (danger)**: 1-3 hari tersisa (Hampir habis)
    -   ⚫ **Gray**: 0 hari tersisa (Habis)
-   ✅ Icon: `heroicon-o-clock`
-   ✅ Sortable
-   ✅ **Tooltip lengkap** menampilkan:
    ```
    Jatah Tahunan: 12 hari
    Terpakai: 10 hari (83.3%)
    Sisa: 2 hari
    ```

## 🔍 Advanced Filter

### Leave Usage Filter

```php
Tables\Filters\SelectFilter::make('leave_usage')
    ->label('Penggunaan Cuti')
    ->options([
        'no_leave' => 'Belum Pernah Cuti',
        'low_usage' => 'Penggunaan Rendah (≤ 3 hari)',
        'medium_usage' => 'Penggunaan Sedang (4-8 hari)',
        'high_usage' => 'Penggunaan Tinggi (> 8 hari)',
        'over_limit' => 'Melebihi Jatah (> 12 hari)',
    ])
```

**Filter Categories:**

-   🟫 **Belum Pernah Cuti**: User yang belum pernah mengambil cuti yang disetujui tahun ini
-   🟢 **Penggunaan Rendah**: 1-3 hari cuti terpakai
-   🟡 **Penggunaan Sedang**: 4-8 hari cuti terpakai
-   🟠 **Penggunaan Tinggi**: 9-12 hari cuti terpakai
-   🔴 **Melebihi Jatah**: >12 hari cuti terpakai

## 💡 Business Logic

### Annual Leave Allowance

-   **Default**: 12 hari per tahun
-   **Scope**: Tahun kalender (Januari - Desember)
-   **Status**: Hanya menghitung leave request dengan status **"approved"**

### Calculation Logic

```php
// Total Leave Taken (Current Year)
$totalTaken = $user->leaveRequests()
    ->where('status', 'approved')
    ->whereYear('start_date', date('Y'))
    ->sum('total_days');

// Remaining Leave
$remainingLeave = max(0, 12 - $totalTaken);

// Usage Percentage
$percentage = round(($totalTaken / 12) * 100, 1);
```

### Color Coding System

#### For Total Leave Taken:

| Days Used | Color  | Status         |
| --------- | ------ | -------------- |
| 0         | Gray   | Belum cuti     |
| 1-6       | Green  | Normal         |
| 7-12      | Yellow | Moderat        |
| >12       | Red    | Melebihi jatah |

#### For Remaining Leave:

| Days Left | Color  | Status       |
| --------- | ------ | ------------ |
| 8-12      | Green  | Masih banyak |
| 4-7       | Yellow | Sedang       |
| 1-3       | Red    | Hampir habis |
| 0         | Gray   | Habis        |

## 🎯 Use Cases

### For HR Management:

1. **Monitor Leave Usage**: Lihat siapa yang sudah/belum menggunakan cuti
2. **Workload Planning**: Identifikasi karyawan dengan sisa cuti banyak (mungkin perlu liburan)
3. **Policy Enforcement**: Monitor yang melebihi jatah tahunan
4. **Resource Planning**: Prediksi kebutuhan coverage saat cuti ramai

### For Managers:

1. **Team Planning**: Lihat availability tim untuk project planning
2. **Approval Insights**: Data untuk pertimbangan approve/reject leave requests
3. **Fairness Monitoring**: Pastikan distribusi cuti yang adil dalam tim

### For Employees (Self-Service):

1. **Personal Tracking**: Monitor penggunaan cuti pribadi
2. **Planning**: Rencanakan cuti berdasarkan sisa jatah

## 🔧 Technical Implementation

### Query Optimization

-   **Efficient Queries**: Menggunakan relationship dan aggregate functions
-   **Year Filtering**: Hanya load data tahun berjalan untuk performa
-   **Lazy Loading**: Data dihitung on-demand saat kolom ditampilkan

### Database Performance

```sql
-- Example query generated
SELECT
    users.*,
    (SELECT COALESCE(SUM(total_days), 0)
     FROM leave_requests
     WHERE user_id = users.id
     AND status = 'approved'
     AND YEAR(start_date) = 2025) as total_leave_taken
FROM users;
```

### Memory Efficiency

-   Menggunakan `getStateUsing()` untuk kalkulasi runtime
-   Tidak menyimpan data redundant di database
-   Lazy calculation hanya saat kolom visible

## 📈 Benefits

### 1. **Visibility**

-   Dashboard overview penggunaan cuti seluruh karyawan
-   Identifikasi pattern dan trend cuti

### 2. **Efficiency**

-   Tidak perlu buka detail user untuk lihat info cuti
-   Filter advanced untuk analisis cepat

### 3. **Insights**

-   Data-driven decision making untuk HR policies
-   Monitor compliance terhadap leave policies

### 4. **User Experience**

-   Visual indicators (badges, colors) untuk quick insights
-   Tooltips dengan detail breakdown
-   Sortable untuk prioritization

## 🚀 Future Enhancements

### Possible Improvements:

1. **Configurable Leave Allowance**: Per role/department different allowances
2. **Leave Types Breakdown**: Separate tracking for sick, annual, emergency leave
3. **Historical Trends**: Multi-year comparison charts
4. **Predictive Analytics**: Suggest optimal leave timing
5. **Team Leave Calendar**: Visual calendar showing team availability
6. **Auto Alerts**: Notify when approaching limits or unused leave
7. **Leave Accrual**: Monthly accrual system instead of annual allowance

## 🔍 Testing Scenarios

### Test Data Validation:

1. **User with no leave**: Should show 0 taken, 12 remaining
2. **User with pending leave**: Pending should not count toward taken
3. **User with rejected leave**: Rejected should not count toward taken
4. **User over limit**: Should show proper color coding and remaining = 0
5. **Cross-year leave**: Should only count current year portion

### Filter Testing:

1. **No Leave Filter**: Should show users with 0 approved leave
2. **Over Limit Filter**: Should show users with >12 days taken
3. **Combination Filters**: Test with role + leave usage filters

This comprehensive leave tracking system provides powerful insights for effective human resource management! 🎉
