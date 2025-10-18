php artisan migrate --path=database/migrations/2025_08_30_094406_create_company_logos_table.php
php artisan shield:generate --all
lanjut blogs php artisan migrate --path=database/migrations/2025_09_01_100318_create_blogs_table.php
php artisan migrate --path=database/migrations/
cd /home/u380354370/domains/maknakreatif.id/public_html

# Server Deployment Commands (Run setelah git pull)

git pull origin main
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
php artisan optimize
