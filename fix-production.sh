#!/bin/bash

echo "🔧 MAKNA FINANCE - PRODUCTION 403 ERROR FIXER"
echo "=============================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}🔍 Checking current configuration...${NC}"

# Check if we're in Laravel directory
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Error: Not in Laravel directory!${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Laravel directory confirmed${NC}"

# Step 1: Clear all caches
echo -e "${YELLOW}🧹 Clearing all caches...${NC}"
php artisan optimize:clear
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Caches cleared successfully${NC}"
else
    echo -e "${RED}❌ Error clearing caches${NC}"
fi

# Step 2: Check and fix permissions
echo -e "${YELLOW}🔐 Fixing file permissions...${NC}"
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod 644 .env

if [ -d "public" ]; then
    chmod -R 755 public
    echo -e "${GREEN}✅ Permissions fixed${NC}"
else
    echo -e "${RED}❌ Public directory not found${NC}"
fi

# Step 3: Check if session table exists
echo -e "${YELLOW}🗄️ Checking session table...${NC}"
php artisan migrate --force
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Database migrations completed${NC}"
else
    echo -e "${RED}❌ Database migration failed${NC}"
fi

# Step 4: Create session table if using database driver
SESSION_DRIVER=$(grep "SESSION_DRIVER=" .env | cut -d'=' -f2)
if [ "$SESSION_DRIVER" = "database" ]; then
    echo -e "${YELLOW}📊 Creating session table for database driver...${NC}"
    php artisan session:table
    php artisan migrate --force
fi

# Step 5: Regenerate production caches
echo -e "${YELLOW}🚀 Regenerating production caches...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Production caches generated${NC}"
else
    echo -e "${RED}❌ Error generating caches${NC}"
fi

# Step 6: Check .env configuration
echo -e "${YELLOW}⚙️ Checking .env configuration...${NC}"

# Check APP_URL
APP_URL=$(grep "APP_URL=" .env | cut -d'=' -f2)
if [[ "$APP_URL" == *"127.0.0.1"* ]] || [[ "$APP_URL" == *"localhost"* ]]; then
    echo -e "${YELLOW}⚠️ WARNING: APP_URL masih development (${APP_URL})${NC}"
    echo -e "${YELLOW}   Untuk production, ubah ke domain sebenarnya${NC}"
fi

# Check LOG_LEVEL
LOG_LEVEL=$(grep "LOG_LEVEL=" .env | cut -d'=' -f2)
if [ "$LOG_LEVEL" = "debug" ]; then
    echo -e "${YELLOW}⚠️ WARNING: LOG_LEVEL masih debug${NC}"
    echo -e "${YELLOW}   Untuk production, ubah ke 'warning' atau 'error'${NC}"
fi

# Step 7: Final checks
echo -e "${YELLOW}🔍 Final checks...${NC}"

# Check if public/.htaccess exists
if [ -f "public/.htaccess" ]; then
    echo -e "${GREEN}✅ public/.htaccess exists${NC}"
else
    echo -e "${RED}❌ public/.htaccess missing!${NC}"
    echo "Creating basic .htaccess..."
    cat > public/.htaccess << 'EOF'
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
EOF
    echo -e "${GREEN}✅ .htaccess created${NC}"
fi

echo ""
echo -e "${GREEN}🎉 PRODUCTION FIX COMPLETED!${NC}"
echo ""
echo -e "${YELLOW}📋 NEXT STEPS:${NC}"
echo "1. Ubah APP_ENV=production di .env"
echo "2. Ubah APP_DEBUG=false di .env"
echo "3. Ubah LOG_LEVEL=warning di .env"
echo "4. Ubah APP_URL ke domain production"
echo "5. Test aplikasi"
echo ""
echo -e "${YELLOW}🔍 JIKA MASIH ERROR 403:${NC}"
echo "1. Cek error log: tail -f storage/logs/laravel.log"
echo "2. Cek web server error log"
echo "3. Pastikan document root mengarah ke folder 'public/'"
echo "4. Hubungi hosting provider jika perlu"
echo ""
echo -e "${YELLOW}💡 TIP: Untuk debugging, sementara set APP_DEBUG=true${NC}"
echo -e "${YELLOW}    kemudian matikan lagi setelah masalah terselesaikan${NC}"
