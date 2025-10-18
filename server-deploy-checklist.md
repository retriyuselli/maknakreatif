# Server Deployment Checklist untuk Fix Spasi Issue

## 🚀 Steps untuk Apply Changes di Server

### 1. Deploy Code ke Server

```bash
cd /home/u380354370/domains/maknakreatif.id/public_html
git pull origin main
```

### 2. Clear All Cache (PENTING!)

```bash
# Clear Laravel cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Clear compiled files
php artisan clear-compiled

# Optimize for production
php artisan optimize:clear
php artisan optimize
```

### 3. Clear OPcache (Jika Ada)

```bash
# Restart PHP-FPM atau web server
# Atau buat file PHP untuk clear OPcache:
# echo "<?php opcache_reset(); echo 'OPcache cleared'; ?>" > clear-opcache.php
# Akses via browser lalu hapus file
```

### 4. Force Refresh Browser

-   Tekan `Ctrl+F5` atau `Cmd+Shift+R`
-   Atau buka dalam incognito/private mode

### 5. Verify File Changes

```bash
# Check apakah file sudah update
cat app/Filament/Resources/BankStatementResource/RelationManagers/BankReconciliationItemsRelationManager.php | grep -A 5 "formatStateUsing"
```

### 6. Database Check (Opsional)

```bash
# Jika masih bermasalah, mungkin data di server berbeda
php artisan tinker
# Lalu test:
# App\Models\BankReconciliationItem::first()->description
```

## 🔍 Troubleshooting

### Jika Masih Bermasalah:

1. **Check File Permissions:**

    ```bash
    chmod -R 755 app/
    chown -R www-data:www-data app/ (sesuaikan user)
    ```

2. **Verify Code Deploy:**

    ```bash
    git log --oneline -5
    git diff HEAD~1 app/Filament/Resources/BankStatementResource/RelationManagers/BankReconciliationItemsRelationManager.php
    ```

3. **Manual Cache Clear:**

    ```bash
    rm -rf bootstrap/cache/*
    rm -rf storage/framework/cache/*
    rm -rf storage/framework/views/*
    ```

4. **Check Error Logs:**
    ```bash
    tail -f storage/logs/laravel.log
    ```

## 📝 Expected Behavior After Fix

-   ✅ Form edit menampilkan text tanpa spasi berlebihan
-   ✅ Table display clean dari excessive whitespace
-   ✅ Data tersimpan dengan format yang rapi

## 🆘 Emergency Commands

Jika semua gagal, coba force update:

```bash
composer dump-autoload
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
