# Leave Management Widget Documentation

## Overview

Kumpulan widget Filament untuk mengelola dan memantau data cuti karyawan secara real-time di dashboard admin.

## Widget List

### 1. LeaveBalanceWidget

**File:** `app/Filament/Widgets/LeaveBalanceWidget.php`
**Type:** Stats Overview Widget
**Sort Order:** 1

#### Features:

-   **Personal Leave Balance**: Menampilkan saldo cuti tahunan dan sakit untuk user yang sedang login
-   **Team Average**: Rata-rata saldo cuti tim/departemen
-   **Low Balance Alerts**: Jumlah karyawan dengan saldo cuti rendah (≤3 hari)
-   **Total Leave Usage**: Total penggunaan cuti tahun ini
-   **Active Employees**: Jumlah karyawan aktif dengan saldo cuti
-   **Department Usage**: Persentase penggunaan cuti departemen

#### Key Methods:

```php
getStats(): array // Mengambil data statistik utama
getCachedPersonalBalance(): array // Cache personal balance
getCachedTeamStats(): array // Cache team statistics
getPersonalLeaveBalance(): array // Saldo cuti personal
getTeamAverageBalance(): array // Rata-rata tim
getLowBalanceAlerts(): array // Alert saldo rendah
```

#### Database Dependencies:

-   `LeaveBalance` model
-   `User` model dengan relasi `leaveBalances`
-   Kolom: `leave_type`, `remaining_days`, `total_days`

---

### 2. LeaveUsageChartWidget

**File:** `app/Filament/Widgets/LeaveUsageChartWidget.php`
**Type:** Chart Widget (Line Chart)
**Sort Order:** 2

#### Features:

-   **Multi-Period Filter**: Year, Quarter, Month view
-   **Leave Type Breakdown**: Annual, Sick, Other leave visualization
-   **Trend Analysis**: Pattern penggunaan cuti dari waktu ke waktu
-   **Interactive Charts**: Tooltip dan legend yang informatif

#### Filter Options:

-   `year`: Data bulanan untuk tahun ini
-   `quarter`: Data quarterly untuk tahun ini
-   `month`: Data harian untuk bulan ini

#### Key Methods:

```php
getData(): array // Main data preparation
getYearlyData(): array // Monthly data for current year
getQuarterlyData(): array // Quarterly aggregation
getMonthlyData(): array // Daily data for current month
getFilters(): array // Filter options
```

#### Chart Configuration:

-   **Type**: Line chart dengan area fill
-   **Colors**: Blue (Annual), Red (Sick), Green (Other)
-   **Responsive**: Auto-height dengan max 400px
-   **Interactive**: Hover effects dan mode nearest

---

### 3. RecentLeaveRequestsWidget

**File:** `app/Filament/Widgets/RecentLeaveRequestsWidget.php`
**Type:** Table Widget
**Sort Order:** 3

#### Features:

-   **Recent Requests**: 10 permintaan cuti terbaru
-   **Quick Actions**: Approve/Reject langsung dari widget
-   **Status Badges**: Visual status dengan warna dan icon
-   **Employee Info**: Detail karyawan dan jenis cuti
-   **Real-time Updates**: Auto-refresh setiap 30 detik

#### Table Columns:

-   `user.name`: Nama karyawan
-   `leaveType.name`: Jenis cuti dengan badge
-   `start_date`, `end_date`: Periode cuti
-   `days_requested`: Jumlah hari dengan color coding
-   `status`: Status dengan icon dan color
-   `reason`: Alasan cuti (toggleable)

#### Actions:

```php
ViewAction // Detail view
Action::make('approve') // Quick approve
Action::make('reject') // Quick reject
```

#### Permissions:

-   Actions visible untuk pending requests
-   Simplified authorization (tidak ada complex policy checking)

---

### 4. EmployeeLeaveOverviewWidget

**File:** `app/Filament/Widgets/EmployeeLeaveOverviewWidget.php`
**Type:** Table Widget  
**Sort Order:** 4

#### Features:

-   **Employee List**: Semua karyawan dengan saldo cuti
-   **Balance Breakdown**: Annual, Sick, Total balance per karyawan
-   **Status Indicators**: Normal/Low/Critical berdasarkan saldo minimum
-   **Color Coding**: Visual indicators untuk quick assessment
-   **Pagination**: 15 records per page

#### Table Columns:

-   `name`: Nama karyawan (searchable, sortable)
-   `email`: Email (hidden by default)
-   `annual_leave_balance`: Saldo cuti tahunan dengan color coding
-   `sick_leave_balance`: Saldo cuti sakit dengan color coding
-   `total_balance`: Total saldo dengan color coding
-   `status`: Status badge (Normal/Low/Critical)

#### Color Logic:

```php
// Annual Leave
>= 15 days: success (green)
>= 10 days: warning (yellow)
< 10 days: danger (red)

// Sick Leave
>= 8 days: success
>= 5 days: warning
< 5 days: danger

// Status
<= 3 days: Critical
<= 7 days: Low
> 7 days: Normal
```

---

## Installation & Configuration

### 1. Register Widgets

Add to your Filament Panel Provider:

```php
// app/Providers/Filament/AdminPanelProvider.php
->widgets([
    App\Filament\Widgets\LeaveBalanceWidget::class,
    App\Filament\Widgets\LeaveUsageChartWidget::class,
    App\Filament\Widgets\RecentLeaveRequestsWidget::class,
    App\Filament\Widgets\EmployeeLeaveOverviewWidget::class,
])
```

### 2. Database Requirements

Ensure these models and relationships exist:

```php
// User Model
public function leaveBalances()
{
    return $this->hasMany(LeaveBalance::class);
}

public function leaveRequests()
{
    return $this->hasMany(LeaveRequest::class);
}

// LeaveBalance Model
public function user()
{
    return $this->belongsTo(User::class);
}

// LeaveRequest Model
public function user()
{
    return $this->belongsTo(User::class);
}

public function leaveType()
{
    return $this->belongsTo(LeaveType::class);
}

public function approvedBy()
{
    return $this->belongsTo(User::class, 'approved_by');
}
```

### 3. Required Database Columns

#### leave_balances table:

-   `user_id` (foreign key)
-   `leave_type` (string: 'annual', 'sick', etc.)
-   `total_days` (integer)
-   `remaining_days` (integer)
-   `year` (integer)

#### leave_requests table:

-   `user_id` (foreign key)
-   `leave_type_id` (foreign key)
-   `start_date` (date)
-   `end_date` (date)
-   `days_requested` (integer)
-   `status` (enum: pending, approved, rejected, cancelled)
-   `reason` (text)
-   `approved_by` (foreign key, nullable)
-   `approved_at` (timestamp, nullable)

---

## Performance Considerations

### Caching Strategy:

-   **LeaveBalanceWidget**: Cache personal dan team stats selama 5 menit
-   **Charts**: Cache query results untuk performa optimal
-   **Tables**: Eager loading relasi untuk menghindari N+1 queries

### Optimization Tips:

1. **Database Indexes**:

    ```sql
    CREATE INDEX idx_leave_balances_user_type ON leave_balances(user_id, leave_type);
    CREATE INDEX idx_leave_requests_status_date ON leave_requests(status, start_date);
    ```

2. **Query Optimization**:

    - Gunakan `with()` untuk eager loading
    - Filter data di database level, bukan collection
    - Implementasi pagination untuk table widget

3. **Real-time Updates**:
    - Poll interval disesuaikan dengan kebutuhan (30s-60s)
    - Cache invalidation saat data berubah

---

## Customization Guide

### Extending Widgets:

```php
// Custom Colors
protected function getCustomColors(): array
{
    return [
        'primary' => '#your-color',
        'success' => '#your-color',
        // ...
    ];
}

// Custom Filters
protected function getCustomFilters(): array
{
    return [
        'department' => 'Department Filter',
        'status' => 'Status Filter',
    ];
}
```

### Adding New Metrics:

```php
// In LeaveBalanceWidget::getStats()
Stat::make('Custom Metric', $this->getCustomMetricValue())
    ->description('Custom description')
    ->descriptionIcon('heroicon-m-custom-icon')
    ->color('warning');
```

---

## Troubleshooting

### Common Issues:

1. **Widget tidak muncul**:

    - Pastikan widget terdaftar di Panel Provider
    - Check permissions dan policies
    - Verify model relationships

2. **Data tidak akurat**:

    - Clear cache: `php artisan cache:clear`
    - Check database constraints
    - Verify leave_type values consistency

3. **Performance lambat**:

    - Implement database indexes
    - Review eager loading strategies
    - Consider reducing poll intervals

4. **Chart tidak tampil**:
    - Pastikan data ada di database
    - Check filter logic
    - Verify Chart.js dependencies

---

## Future Enhancements

### Planned Features:

1. **Export Functionality**: PDF/Excel export untuk reports
2. **Advanced Filters**: Department, date range, employee filters
3. **Notifications**: Real-time alerts untuk approval
4. **Dashboard Customization**: User-configurable widget layout
5. **Mobile Optimization**: Responsive design improvements
6. **Integration**: Calendar integration untuk visual planning

### API Extensions:

```php
// Potential API endpoints
GET /api/leave-stats/{user}
GET /api/department-overview/{department}
POST /api/leave-requests/{id}/approve
```

---

## Support & Maintenance

-   **Version**: 1.0.0
-   **Laravel**: ^10.0
-   **Filament**: ^3.0
-   **PHP**: ^8.1

For issues and feature requests, please refer to the project documentation or contact the development team.
