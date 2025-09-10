# Fitur Jumlah Role pada Tabel User

## Deskripsi

Telah ditambahkan kolom "Jumlah Role" pada tabel UserResource yang menampilkan berapa banyak role yang dimiliki oleh setiap user.

## Fitur yang Ditambahkan

### 1. Kolom Jumlah Role

-   **Label**: "Jumlah Role"
-   **Format**: Menampilkan angka + "Role" atau "Roles" (contoh: "1 Role", "3 Roles")
-   **Badge**: Ya, dengan warna berbeda berdasarkan jumlah role
-   **Icon**: heroicon-o-user-group
-   **Sortable**: Ya, dapat diurutkan berdasarkan jumlah role

### 2. Color Coding

-   **Gray**: 0 role (tidak ada role)
-   **Success (Green)**: 1 role
-   **Warning (Yellow)**: 2 role
-   **Danger (Red)**: 3 atau lebih role

### 3. Tooltip

-   Menampilkan daftar lengkap nama role saat hover
-   Format: "Roles: admin, employee" atau "Tidak ada role"

### 4. Sorting

-   Kolom dapat diurutkan (ascending/descending)
-   Menggunakan database count untuk performa optimal

## Implementasi Teknis

### Database Query Enhancement

```php
->with('roles') // Load roles for display and counting
->withCount('roles') // Add roles count for sorting and display
```

### Column Definition

```php
Tables\Columns\TextColumn::make('roles_count')
    ->label('Jumlah Role')
    ->getStateUsing(function (User $record): string {
        $count = $record->roles()->count();
        return $count . ' Role' . ($count > 1 ? 's' : '');
    })
    ->badge()
    ->color(function (User $record): string {
        $count = $record->roles()->count();
        return match (true) {
            $count === 0 => 'gray',
            $count === 1 => 'success',
            $count === 2 => 'warning',
            $count >= 3 => 'danger',
            default => 'primary',
        };
    })
    ->sortable(function (Builder $query, string $direction): Builder {
        return $query
            ->withCount('roles')
            ->orderBy('roles_count', $direction);
    })
    ->icon('heroicon-o-user-group')
    ->tooltip(function (User $record): string {
        $roles = $record->roles->pluck('name')->toArray();
        return empty($roles) ? 'Tidak ada role' : 'Roles: ' . implode(', ', $roles);
    })
```

## Manfaat

1. **Visibilitas**: Admin dapat dengan mudah melihat berapa banyak role yang dimiliki setiap user
2. **Identifikasi**: Mudah mengidentifikasi user dengan multiple roles atau tanpa role
3. **Sorting**: Dapat mengurutkan user berdasarkan jumlah role untuk analisis
4. **Color Coding**: Visual indicator yang memudahkan identifikasi cepat
5. **Detail Information**: Tooltip memberikan informasi lengkap tentang role yang dimiliki

## File yang Dimodifikasi

-   `app/Filament/Resources/UserResource.php`
    -   Menambahkan kolom `roles_count`
    -   Meningkatkan query dengan `withCount('roles')`
    -   Menambahkan `with('roles')` untuk eager loading

## Testing

-   Kolom baru akan muncul di tabel User Resource
-   Badge akan menampilkan warna sesuai jumlah role
-   Sorting akan berfungsi dengan baik
-   Tooltip akan menampilkan detail role saat hover
-   Performa tetap optimal dengan eager loading
