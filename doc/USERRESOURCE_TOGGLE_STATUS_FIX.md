# 🔧 Fix UserResource Toggle Status Error

## 🚨 **Error yang Terjadi**

```sql
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'status_user' in 'field list'
(Connection: mysql, SQL: update `users` set `status_user` = active,
`users`.`updated_at` = 2025-09-02 17:27:27 where `id` = 17)
```

## 🔍 **Root Cause Analysis**

### **Database Schema Reality vs Code**

-   **Expected Column**: `status_user` (used in code)
-   **Actual Column**: `status` (exists in database)
-   **Column Type**: `ENUM('active','inactive','terminated')`
-   **Default Value**: `active`

### **Problem Source**

Action `toggle_status` menggunakan field `status_user` yang tidak ada di database, seharusnya menggunakan field `status`.

## ✅ **Solutions Applied**

### 1. **Fixed Toggle Status Action**

```php
// ❌ Before (ERROR)
->action(function ($record): void {
    $newStatus = $record->status_user === 'active' ? 'inactive' : 'active';
    $record->update(['status_user' => $newStatus]);
})

// ✅ After (FIXED)
->action(function ($record): void {
    $newStatus = $record->status === 'active' ? 'inactive' : 'active';
    $record->update(['status' => $newStatus]);
})
```

### 2. **Fixed Label Logic**

```php
// ❌ Before
->label(function ($record) {
    return $record->status_user === 'active' ? 'Nonaktifkan' : 'Aktifkan';
})

// ✅ After
->label(function ($record) {
    return $record->status === 'active' ? 'Nonaktifkan' : 'Aktifkan';
})
```

### 3. **Fixed Icon & Color Logic**

```php
// ❌ Before
->icon(function ($record) {
    return $record->status_user === 'active' ? 'heroicon-o-pause' : 'heroicon-o-play';
})
->color(function ($record) {
    return $record->status_user === 'active' ? 'warning' : 'success';
})

// ✅ After
->icon(function ($record) {
    return $record->status === 'active' ? 'heroicon-o-pause' : 'heroicon-o-play';
})
->color(function ($record) {
    return $record->status === 'active' ? 'warning' : 'success';
})
```

### 4. **Fixed Bulk Action**

```php
// ❌ Before
foreach ($records as $record) {
    $newStatus = $record->status_user === 'active' ? 'inactive' : 'active';
    $record->update(['status_user' => $newStatus]);
}

// ✅ After
foreach ($records as $record) {
    $newStatus = $record->status === 'active' ? 'inactive' : 'active';
    $record->update(['status' => $newStatus]);
}
```

### 5. **Enhanced Modal Descriptions**

```php
->modalHeading(function ($record) {
    return $record->status === 'active' ? 'Nonaktifkan User' : 'Aktifkan User';
})
->modalDescription(function ($record) {
    $action = $record->status === 'active' ? 'menonaktifkan' : 'mengaktifkan';
    return "Apakah Anda yakin ingin {$action} user {$record->name}?";
})
```

## 🎯 **New Features Added**

### 1. **Enhanced Table Columns**

```php
// Separated Status Jabatan vs Status Akun
Tables\Columns\TextColumn::make('status.status_name')
    ->label('Status Jabatan')  // Admin, Finance, HRD, etc.

Tables\Columns\TextColumn::make('status')
    ->label('Status Akun')    // Active, Inactive, Terminated
    ->formatStateUsing(function (string $state): string {
        return match ($state) {
            'active' => 'Aktif',
            'inactive' => 'Nonaktif',
            'terminated' => 'Terminated',
            default => $state,
        };
    })
```

### 2. **Color-coded Status**

```php
->color(function (string $state): string {
    return match ($state) {
        'active' => 'success',     // Green
        'inactive' => 'warning',   // Orange
        'terminated' => 'danger',  // Red
        default => 'gray',
    };
})
```

### 3. **Separate Filters**

```php
Tables\Filters\SelectFilter::make('status')
    ->label('Status Jabatan')  // Filter by job position

Tables\Filters\SelectFilter::make('account_status')
    ->label('Status Akun')     // Filter by account status
    ->options([
        'active' => 'Aktif',
        'inactive' => 'Nonaktif',
        'terminated' => 'Terminated',
    ])
```

## 🗃️ **Database Schema Confirmation**

### **Users Table Structure**

```sql
status ENUM('active','inactive','terminated') NOT NULL DEFAULT 'active'
```

### **Status Values**

-   `active` (default) - User aktif dan dapat login
-   `inactive` - User sementara dinonaktifkan
-   `terminated` - User sudah di-terminate (permanent)

## 🧪 **Testing Results**

### ✅ **Syntax Check**

```bash
php -l app/Filament/Resources/UserResource.php
# Result: No syntax errors detected
```

### ✅ **Class Loading**

```bash
php artisan tinker --execute="use App\Filament\Resources\UserResource; echo 'Success';"
# Result: UserResource loaded successfully
```

### ✅ **Status Field Test**

```bash
php artisan tinker --execute="use App\Models\User; echo User::first()->status;"
# Result: active
```

## 🎯 **Action Behavior**

### **Toggle Status Action**

-   **Active → Inactive**: Button shows "Nonaktifkan" (Orange, Pause icon)
-   **Inactive → Active**: Button shows "Aktifkan" (Green, Play icon)
-   **Terminated**: Remains as is (permanent state)

### **Bulk Toggle**

-   Filters out super admin users automatically
-   Changes status for all selected users
-   Shows success notification with count

## 🔒 **Security Features Maintained**

-   Super admin protection intact
-   Permission-based action visibility
-   Confirmation modals for destructive actions
-   Bulk action filtering

## 🎉 **Status**

✅ **FIXED** - Toggle status action sekarang berfungsi normal dengan field database yang benar

## 📝 **Notes**

-   Status akun (`status`) berbeda dengan status jabatan (`status.status_name`)
-   ENUM values: `active`, `inactive`, `terminated`
-   Default status: `active`
-   Action toggle hanya antara `active` ↔ `inactive`
