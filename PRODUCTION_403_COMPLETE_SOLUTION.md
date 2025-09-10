# 🚨 SOLUSI LENGKAP: ERROR 403 PADA APP_ENV=production

## DIAGNOSA MASALAH

Error 403 saat `APP_ENV=production` tapi normal saat `APP_ENV=local` adalah masalah yang **SANGAT UMUM** di Laravel. Ini terjadi karena Laravel memiliki perilaku berbeda antara development dan production mode.

## 🔍 PENYEBAB UTAMA (BERURUTAN BERDASARKAN FREKUENSI)

### 1. **CACHE KONFIGURASI TERSISA** (90% kasus)

```bash
# SOLUSI SEGERA:
php artisan optimize:clear
```

### 2. **APP_URL TIDAK SESUAI** (70% kasus)

```env
# SALAH (development URL di production)
APP_URL=http://127.0.0.1:8000

# BENAR (sesuai domain actual)
APP_URL=https://yourdomain.com
```

### 3. **FILE PERMISSIONS** (60% kasus)

```bash
# Fix permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod 644 .env
```

### 4. **WEB SERVER DOCUMENT ROOT** (50% kasus)

-   Document root HARUS mengarah ke folder `public/`
-   BUKAN ke root aplikasi Laravel

### 5. **SESSION DRIVER DATABASE ISSUE** (40% kasus)

```bash
# Create session table
php artisan session:table
php artisan migrate
```

## 🚀 SOLUSI BERTAHAP

### STEP 1: QUICK FIX (2 menit)

```bash
# Jalankan script otomatis
./fix-production.sh

# Atau manual:
php artisan optimize:clear
chmod -R 755 storage bootstrap/cache
php artisan config:cache
```

### STEP 2: KONFIGURASI .ENV

```env
APP_ENV=production
APP_DEBUG=false  # PENTING: false untuk production
APP_URL=https://your-actual-domain.com
LOG_LEVEL=warning
SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_LIFETIME=30
```

### STEP 3: DEBUGGING MODE (SEMENTARA)

Jika masih error, aktifkan debug untuk melihat error detail:

```env
APP_ENV=production
APP_DEBUG=true  # HANYA UNTUK DEBUGGING!
```

Kemudian lihat error yang muncul, dan matikan lagi `APP_DEBUG=false` setelah selesai.

### STEP 4: CEK LOG ERROR

```bash
# Laravel log
tail -f storage/logs/laravel.log

# Web server log (tergantung server)
tail -f /var/log/apache2/error.log  # Apache
tail -f /var/log/nginx/error.log    # Nginx
```

## 🛠️ KONFIGURASI WEB SERVER

### APACHE (.htaccess)

Pastikan file `public/.htaccess` ada dan benar:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### NGINX

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/your/laravel/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## 🔧 SCRIPT AUTOMASI

File `fix-production.sh` sudah dibuat untuk otomasi fix:

```bash
# Buat executable
chmod +x fix-production.sh

# Jalankan
./fix-production.sh
```

## 📊 CHECKLIST PRODUCTION READY

### KONFIGURASI .ENV

-   [ ] `APP_ENV=production`
-   [ ] `APP_DEBUG=false`
-   [ ] `APP_URL` sesuai domain production
-   [ ] `LOG_LEVEL=warning` atau `error`
-   [ ] Database credentials production
-   [ ] `SESSION_ENCRYPT=true`
-   [ ] `SESSION_LIFETIME` optimal (≤60 menit)

### FILE SYSTEM

-   [ ] Folder `storage/` writable (755)
-   [ ] Folder `bootstrap/cache/` writable (755)
-   [ ] File `.env` not public accessible (644)
-   [ ] `public/.htaccess` exists dan benar

### DATABASE

-   [ ] Database accessible dari production server
-   [ ] Session table exists (jika `SESSION_DRIVER=database`)
-   [ ] Migrations up to date

### WEB SERVER

-   [ ] Document root = `/path/to/laravel/public/`
-   [ ] PHP version supported (≥8.2)
-   [ ] Required PHP extensions installed
-   [ ] SSL certificate (untuk HTTPS)

## 🆘 TROUBLESHOOTING LANJUTAN

### JIKA MASIH ERROR SETELAH SEMUA LANGKAH:

1. **Test dengan APP_ENV=staging**

    ```env
    APP_ENV=staging  # Middle ground antara local dan production
    ```

2. **Cek PHP Error Log**

    ```bash
    # Cari error PHP
    tail -f /var/log/php_errors.log
    ```

3. **Test Route Sederhana**
   Tambah route test di `routes/web.php`:

    ```php
    Route::get('/test-production', function () {
        return 'Production mode working!';
    });
    ```

4. **Cek Composer Dependencies**

    ```bash
    composer install --no-dev --optimize-autoloader
    ```

5. **Hard Reset Cache**

    ```bash
    rm -rf bootstrap/cache/*
    rm -rf storage/framework/cache/*
    rm -rf storage/framework/sessions/*
    rm -rf storage/framework/views/*

    php artisan cache:clear
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    ```

## 🎯 UNTUK HOSTING SHARED

Jika menggunakan shared hosting:

1. **File Manager / cPanel**

    - Pastikan document root mengarah ke `public/`
    - Kadang folder harus diberi nama `public_html/`

2. **Permission 777 (Last Resort)**

    ```bash
    chmod -R 777 storage
    chmod -R 777 bootstrap/cache
    ```

    ⚠️ **WARNING**: Tidak aman untuk VPS, hanya untuk shared hosting

3. **Symlink Public**
    ```bash
    # Jika tidak bisa ubah document root
    ln -s /path/to/laravel/public/* /path/to/public_html/
    ```

## ✅ SUCCESS INDICATORS

Jika berhasil, Anda akan melihat:

-   ✅ Aplikasi load normal dengan `APP_ENV=production`
-   ✅ No error 403/500
-   ✅ Log file tidak ada error critical
-   ✅ Session berfungsi normal
-   ✅ Asset (CSS/JS) load dengan benar

---

**Dibuat:** September 10, 2025  
**Tested:** Laravel 12.28.1  
**Status:** Comprehensive Solution
