# LeaveRequestResource - Permission Documentation

## 🔒 Permission Control Implementation

### Overview

LeaveRequestResource telah diupdate dengan kontrol akses yang membedakan antara user biasa dan super_admin.

## 🎯 Permission Rules

### 1. **Employee Selection (user_id field)**

```php
->disabled(function () {
    $user = Auth::user();
    return $user ? !$user->roles->contains('name', 'super_admin') : true;
})
```

-   **User biasa**: Field disabled, hanya bisa mengajukan cuti untuk dirinya sendiri
-   **Super Admin**: Field aktif, bisa mengajukan cuti untuk semua karyawan

### 2. **Approval Information Section**

```php
->visible(function () {
    $user = Auth::user();
    return $user ? $user->roles->contains('name', 'super_admin') : false;
})
```

-   **User biasa**: Section tidak terlihat
-   **Super Admin**: Section terlihat dan bisa mengubah status approval

### 3. **Table Data Filtering**

```php
->modifyQueryUsing(function (Builder $query) {
    $user = Auth::user();
    if ($user && !$user->roles->contains('name', 'super_admin')) {
        $query->where('user_id', $user->id);
    }
})
```

-   **User biasa**: Hanya melihat leave request miliknya sendiri
-   **Super Admin**: Melihat semua leave request

### 4. **Action Buttons (Approve/Reject)**

```php
->visible(function (LeaveRequest $record) {
    $user = Auth::user();
    $isSuperAdmin = $user ? $user->roles->contains('name', 'super_admin') : false;
    return $record->status === 'pending' && $isSuperAdmin;
})
```

-   **User biasa**: Tombol approve/reject tidak terlihat
-   **Super Admin**: Tombol terlihat untuk record dengan status pending

### 5. **Bulk Actions**

```php
->visible(function () {
    $user = Auth::user();
    return $user ? $user->roles->contains('name', 'super_admin') : false;
})
```

-   **User biasa**: Bulk actions tidak terlihat
-   **Super Admin**: Semua bulk actions tersedia

### 6. **Delete Action**

```php
->visible(function () {
    $user = Auth::user();
    return $user ? $user->roles->contains('name', 'super_admin') : false;
})
```

-   **User biasa**: Tidak bisa delete leave request
-   **Super Admin**: Bisa delete leave request

## 🚀 Features by Role

### User Biasa (Employee)

✅ **Dapat melakukan:**

-   Melihat leave request miliknya sendiri
-   Membuat leave request baru (otomatis untuk dirinya sendiri)
-   Edit leave request miliknya (sebelum diapprove)
-   View detail leave request miliknya

❌ **Tidak dapat melakukan:**

-   Melihat leave request karyawan lain
-   Mengubah status approval
-   Approve/reject leave request
-   Delete leave request
-   Bulk actions
-   Mengajukan cuti untuk karyawan lain

### Super Admin

✅ **Dapat melakukan:**

-   Melihat semua leave request
-   Membuat leave request untuk semua karyawan
-   Edit semua leave request
-   Approve/reject leave request
-   Delete leave request
-   Bulk approve/reject
-   Mengubah status approval
-   Menambahkan approval notes

## 🔧 Implementation Details

### Permission Check Method

```php
$user = Auth::user();
$isSuperAdmin = $user ? $user->roles->contains('name', 'super_admin') : false;
```

### Safe Role Checking

Menggunakan `roles->contains('name', 'super_admin')` untuk menghindari error undefined method pada static analysis.

### Default Behavior

-   Default user_id untuk user login saat ini
-   Default status 'pending' untuk leave request baru
-   Auto-calculation total days berdasarkan start/end date

## 📊 Data Security

1. **Query Level Filtering**: Data difilter di level query untuk performa dan keamanan
2. **UI Level Control**: Elemen UI disembunyikan berdasarkan role
3. **Action Level Protection**: Actions hanya tersedia untuk role yang tepat
4. **Form Level Validation**: Field disabled/enabled berdasarkan permission

## 🔍 Testing Scenarios

### Test sebagai User Biasa:

1. Login sebagai karyawan biasa
2. Akses `/admin/leave-requests`
3. Verifikasi hanya melihat data sendiri
4. Verifikasi tidak ada tombol approve/reject
5. Verifikasi tidak bisa mengajukan cuti untuk orang lain

### Test sebagai Super Admin:

1. Login sebagai super_admin
2. Akses `/admin/leave-requests`
3. Verifikasi melihat semua data
4. Verifikasi ada tombol approve/reject
5. Verifikasi bisa mengajukan cuti untuk semua karyawan
6. Test bulk actions

## 🎉 Benefits

1. **Security**: Data terisolasi berdasarkan role
2. **User Experience**: Interface yang relevan untuk setiap role
3. **Performance**: Query filtering mengurangi load data
4. **Maintainability**: Permission logic terpusat dan konsisten
5. **Scalability**: Mudah menambah role baru di masa depan
