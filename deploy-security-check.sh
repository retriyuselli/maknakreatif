#!/bin/bash

# Deployment Security Checklist Script
# Jalankan script ini di server setelah deploy

echo "🔧 MAKNA FINANCE - SECURITY DEPLOYMENT CHECKLIST"
echo "================================================"

# Check if .env exists
if [ ! -f .env ]; then
    echo "❌ File .env tidak ditemukan!"
    echo "💡 Copy dari .env.production.example dan sesuaikan konfigurasi"
    exit 1
fi

echo "✅ File .env ditemukan"

# Check critical security settings
echo ""
echo "🔍 Memeriksa konfigurasi keamanan..."

# Check APP_DEBUG
if grep -q "APP_DEBUG=true" .env; then
    echo "❌ CRITICAL: APP_DEBUG masih true! Ubah ke false untuk production"
else
    echo "✅ APP_DEBUG sudah false"
fi

# Check APP_ENV
if grep -q "APP_ENV=local" .env; then
    echo "❌ WARNING: APP_ENV masih local! Ubah ke production"
else
    echo "✅ APP_ENV sudah production"
fi

# Check SESSION_ENCRYPT
if grep -q "SESSION_ENCRYPT=false" .env; then
    echo "❌ SECURITY: SESSION_ENCRYPT masih false! Ubah ke true"
else
    echo "✅ SESSION_ENCRYPT sudah true"
fi

# Check SESSION_LIFETIME
LIFETIME=$(grep "SESSION_LIFETIME=" .env | cut -d'=' -f2)
if [ "$LIFETIME" -gt 60 ]; then
    echo "⚠️  WARNING: SESSION_LIFETIME=$LIFETIME menit (disarankan ≤ 60 menit)"
else
    echo "✅ SESSION_LIFETIME sudah optimal ($LIFETIME menit)"
fi

# Check database credentials
if grep -q "DB_PASSWORD=root" .env; then
    echo "❌ CRITICAL: Database password masih default! Ganti segera"
else
    echo "✅ Database password sudah diubah"
fi

echo ""
echo "🚀 LANGKAH SELANJUTNYA:"
echo "1. Perbaiki semua item yang ditandai ❌"
echo "2. Jalankan: php artisan config:cache"
echo "3. Jalankan: php artisan view:cache"
echo "4. Restart web server"

echo ""
echo "📋 CHECKLIST MANUAL TAMBAHAN:"
echo "- [ ] File permissions: chmod 644 .env"
echo "- [ ] Web server config: .env tidak accessible via web"
echo "- [ ] SSL certificate sudah terpasang"
echo "- [ ] Backup database sudah dijadwalkan"
