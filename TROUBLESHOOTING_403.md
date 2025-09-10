# 🚨 TROUBLESHOOTING: ERROR 403 SAAT APP_ENV=production

## MASALAH

Setiap mengubah `APP_ENV=production` aplikasi menampilkan error 403 Forbidden, sedangkan dengan `APP_ENV=local` berjalan normal.

## PENYEBAB UMUM & SOLUSI

### 1. **CACHE KONFIGURASI TERSISA**

**Penyebab:** Cache config masih menyimpan setting lama
**Solusi:**

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### 2. **FILE PERMISSIONS**

**Penyebab:** Permissions tidak tepat untuk production mode
**Solusi:**

```bash
# Set permissions yang benar
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod 644 .env

# Untuk shared hosting kadang perlu 777 (tidak direkomendasikan untuk VPS)
chmod -R 777 storage
chmod -R 777 bootstrap/cache
```

### 3. **WEB SERVER CONFIGURATION**

**Penyebab:** Document root tidak mengarah ke folder public/
**Solusi:**

-   Pastikan web server mengarah ke folder `public/`
-   Bukan ke root aplikasi Laravel

### 4. **APP_URL TIDAK SESUAI**

**Penyebab:** APP_URL tidak sesuai dengan domain actual
**Solusi:**

```env
# Development
APP_URL=http://127.0.0.1:8000

# Production
APP_URL=https://yourdomain.com
```

### 5. **MIDDLEWARE SECURITY**

**Penyebab:** Middleware keamanan lebih ketat di production
**Solusi:** Cek middleware di `app/Http/Kernel.php`

### 6. **SESSION DRIVER ISSUE**

**Penyebab:** Session database table tidak ada atau tidak accessible
**Solusi:**

```bash
php artisan session:table
php artisan migrate
```

### 7. **LOG LEVEL DEBUG**

**Penyebab:** Log level debug dengan APP_ENV=production conflict
**Solusi:**

```env
# Untuk production
LOG_LEVEL=warning
```

## 🔧 LANGKAH DEBUGGING SISTEMATIS

### Step 1: Cek Log Error

```bash
tail -f storage/logs/laravel.log
```

### Step 2: Test Dengan APP_DEBUG=true Sementara

```env
APP_ENV=production
APP_DEBUG=true  # HANYA UNTUK DEBUGGING - MATIKAN SETELAH SELESAI
```

### Step 3: Clear Semua Cache

```bash
php artisan optimize:clear
```

### Step 4: Cek Web Server Error Log

```bash
# Apache
tail -f /var/log/apache2/error.log

# Nginx
tail -f /var/log/nginx/error.log
```

## 🚀 SCRIPT OTOMATIS UNTUK FIX

Jalankan script berikut untuk perbaikan otomatis:

```bash
#!/bin/bash
echo "🔧 Fixing Laravel 403 in Production Mode..."

# Clear all caches
php artisan optimize:clear

# Set proper permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod 644 .env

# Regenerate cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Check if session table exists
php artisan migrate --force

echo "✅ Done! Try accessing your application now."
```

## ⚠️ CHECKLIST PRODUCTION READY

-   [ ] APP_ENV=production
-   [ ] APP_DEBUG=false
-   [ ] LOG_LEVEL=warning atau error
-   [ ] APP_URL sesuai domain production
-   [ ] File permissions benar (755/644)
-   [ ] Web server document root = public/
-   [ ] Database accessible
-   [ ] Session table exists
-   [ ] Cache cleared dan regenerated

## 🆘 JIKA MASIH ERROR

1. **Aktifkan debug sementara** untuk melihat error detail
2. **Cek server error logs**
3. **Test dengan APP_ENV=staging** sebagai middle ground
4. **Hubungi hosting provider** jika di shared hosting

---

**Dibuat:** September 10, 2025  
**Status:** Active Issue
