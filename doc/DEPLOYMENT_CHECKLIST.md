# Production Deployment Checklist - Grand Total Implementation

## 🚀 Pre-Deployment Checklist

### [ ] 1. Environment Preparation

-   [ ] Staging environment matches production
-   [ ] Database backup completed
-   [ ] All dependencies up to date
-   [ ] Laravel caching cleared

### [ ] 2. Code Review

-   [ ] All grand_total related code reviewed
-   [ ] Model events tested
-   [ ] Filament integration verified
-   [ ] Performance impact assessed

### [ ] 3. Testing Verification

-   [ ] Unit tests passing
-   [ ] Integration tests completed
-   [ ] Manual testing in staging
-   [ ] Performance benchmarks recorded

## 🛠️ Deployment Steps

### Step 1: Database Preparation

```bash
# 1.1 Create database backup
mysqldump -u [username] -p [database_name] > backup_pre_grand_total_$(date +%Y%m%d_%H%M%S).sql

# 1.2 Verify backup integrity
mysql -u [username] -p -e "USE [database_name]; SELECT COUNT(*) FROM orders;"

# 1.3 Check current grand_total column status
php artisan tinker --execute="
use Illuminate\Support\Facades\Schema;
echo 'Grand total column exists: ' . (Schema::hasColumn('orders', 'grand_total') ? 'YES' : 'NO') . PHP_EOL;
"
```

### Step 2: Code Deployment

```bash
# 2.1 Pull latest code
git pull origin main

# 2.2 Install/update dependencies
composer install --no-dev --optimize-autoloader

# 2.3 Clear and optimize caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Step 3: Database Migration & Data Population

```bash
# 3.1 Run migrations (if grand_total column doesn't exist)
php artisan migrate --force

# 3.2 Populate grand_total for all existing orders
php artisan orders:update-grand-totals --force

# 3.3 Verify data integrity
php artisan tinker --execute="
\$totalOrders = \App\Models\Order::count();
\$ordersWithGrandTotal = \App\Models\Order::whereNotNull('grand_total')->where('grand_total', '!=', 0)->count();
\$ordersWithoutGrandTotal = \App\Models\Order::whereNull('grand_total')->orWhere('grand_total', 0)->count();
echo 'Total orders: ' . \$totalOrders . PHP_EOL;
echo 'Orders with grand_total: ' . \$ordersWithGrandTotal . PHP_EOL;
echo 'Orders without grand_total: ' . \$ordersWithoutGrandTotal . PHP_EOL;
"
```

### Step 4: Account Manager Targets Update

```bash
# 4.1 Update all AccountManagerTarget calculations
php artisan targets:generate --update

# 4.2 Verify target calculations
php artisan tinker --execute="
\$targets = \App\Models\AccountManagerTarget::with('user')->take(5)->get();
foreach (\$targets as \$target) {
    echo \$target->user->name . ' - ' . \$target->year . '/' . \$target->month . ': ' . number_format(\$target->achieved_amount) . PHP_EOL;
}
"
```

## 🧪 Post-Deployment Testing

### [ ] 1. Functional Testing

```bash
# Test 1: Create new order
php artisan tinker --execute="
\$order = new \App\Models\Order();
\$order->fill([
    'prospect_id' => 1,
    'name' => 'Test Order',
    'slug' => 'test-order-' . time(),
    'number' => 'MW-' . rand(100000, 999999),
    'user_id' => 1,
    'employee_id' => 1,
    'no_kontrak' => 'TEST-001',
    'pax' => 100,
    'status' => 'pending',
    'total_price' => 10000000,
    'promo' => 500000,
    'penambahan' => 200000,
    'pengurangan' => 100000
]);
\$order->save();
echo 'Expected grand_total: ' . (10000000 + 200000 - 500000 - 100000) . PHP_EOL;
echo 'Actual grand_total: ' . \$order->grand_total . PHP_EOL;
echo 'Test: ' . (\$order->grand_total == 9600000 ? 'PASSED' : 'FAILED') . PHP_EOL;
"

# Test 2: Update existing order
php artisan tinker --execute="
\$order = \App\Models\Order::first();
\$oldGrandTotal = \$order->grand_total;
\$order->promo = (\$order->promo ?? 0) + 100000;
\$order->save();
\$newGrandTotal = \$order->grand_total;
echo 'Grand total updated: ' . (\$newGrandTotal == \$oldGrandTotal - 100000 ? 'PASSED' : 'FAILED') . PHP_EOL;
"
```

### [ ] 2. Performance Testing

```bash
# Test AccountManagerTarget query performance
php artisan tinker --execute="
\$start = microtime(true);
\$targets = \App\Models\AccountManagerTarget::with('user')->get();
\$end = microtime(true);
echo 'AccountManagerTarget query time: ' . round((\$end - \$start) * 1000, 2) . 'ms' . PHP_EOL;
echo 'Total targets loaded: ' . \$targets->count() . PHP_EOL;
"
```

### [ ] 3. UI Testing

-   [ ] Open OrderResource in admin panel
-   [ ] Create new order and verify grand_total calculation
-   [ ] Edit existing order and verify real-time updates
-   [ ] Check AccountManagerTarget resource performance

## 📊 Monitoring Setup

### Key Metrics to Monitor

1. **Database Performance**

    - Query response time for orders table
    - Index usage on grand_total column
    - Overall database CPU usage

2. **Application Performance**

    - OrderResource page load time
    - AccountManagerTarget resource performance
    - Form submission response time

3. **Data Integrity**
    - Count of orders with null grand_total
    - Validation of grand_total calculations
    - Account manager achievement accuracy

### Monitoring Commands

```bash
# Daily data integrity check
php artisan tinker --execute="
\$nullGrandTotals = \App\Models\Order::whereNull('grand_total')->count();
\$invalidGrandTotals = \App\Models\Order::whereRaw('grand_total != (total_price + COALESCE(penambahan, 0) - COALESCE(promo, 0) - COALESCE(pengurangan, 0))')->count();
echo 'Orders with null grand_total: ' . \$nullGrandTotals . PHP_EOL;
echo 'Orders with invalid grand_total: ' . \$invalidGrandTotals . PHP_EOL;
"

# Performance monitoring
php artisan tinker --execute="
\$start = microtime(true);
\$sum = \App\Models\Order::sum('grand_total');
\$end = microtime(true);
echo 'Sum of all grand_totals: IDR ' . number_format(\$sum) . PHP_EOL;
echo 'Query time: ' . round((\$end - \$start) * 1000, 2) . 'ms' . PHP_EOL;
"
```

## 🚨 Rollback Plan

### If Issues Detected:

#### Option 1: Quick Fix (Minor Issues)

```bash
# Re-run grand_total calculation
php artisan orders:update-grand-totals --force
php artisan targets:generate --update
```

#### Option 2: Partial Rollback (Form Issues)

```bash
# Revert only Filament changes, keep database column
git checkout HEAD~1 -- app/Filament/Resources/OrderResource.php
php artisan config:clear
php artisan view:clear
```

#### Option 3: Full Rollback (Major Issues)

```bash
# 1. Restore database backup
mysql -u [username] -p [database_name] < backup_pre_grand_total_YYYYMMDD_HHMMSS.sql

# 2. Revert code changes
git revert [commit_hash]

# 3. Clear caches
php artisan optimize:clear
php artisan config:cache
```

## ✅ Success Criteria

Deployment is successful when:

-   [ ] All existing orders have calculated grand_total values
-   [ ] New orders automatically calculate grand_total on save
-   [ ] Order forms update grand_total in real-time
-   [ ] AccountManagerTarget queries use grand_total column
-   [ ] No performance degradation observed
-   [ ] All tests passing
-   [ ] No data integrity issues

## 📞 Emergency Contacts

-   **Primary Developer**: [Your Name]
-   **Database Admin**: [DBA Name]
-   **DevOps Team**: [DevOps Contact]
-   **Business Stakeholder**: [Business Contact]

## 📋 Post-Deployment Report Template

```
Deployment Date: [DATE]
Deployed By: [NAME]
Deployment Duration: [TIME]

Results:
- Orders updated: [COUNT]
- Performance improvement: [PERCENTAGE]
- Issues encountered: [DESCRIPTION]
- Resolution: [ACTIONS TAKEN]

Next Steps:
- Monitor for 24 hours
- Schedule weekly data integrity checks
- Review performance metrics after 1 week
```

---

**⚠️ IMPORTANT**: Keep this checklist during deployment and mark each item as completed. Document any deviations or issues encountered.
