# Grand Total Migration Guide

## 🎯 Executive Summary

This document provides step-by-step instructions for migrating the `grand_total` calculation from a computed attribute to a persistent database column in the production environment.

## ⏰ Migration Timeline

**Estimated Duration**: 2-4 hours  
**Recommended Time**: During low-traffic hours (typically 2:00 AM - 6:00 AM)  
**Team Required**: 1 Developer, 1 Database Administrator

## 🚨 Pre-Migration Requirements

### 1. Environment Verification

-   [ ] Production database backup completed
-   [ ] Staging environment tested successfully
-   [ ] All team members notified
-   [ ] Maintenance window scheduled

### 2. Resource Requirements

-   [ ] Database server has sufficient disk space (minimum 10% free)
-   [ ] Application server resources available
-   [ ] Monitoring tools active

### 3. Backup Strategy

```bash
# Create comprehensive backup
mysqldump -u [username] -p [database] \
  --single-transaction \
  --routines \
  --triggers \
  --events \
  > backup_$(date +%Y%m%d_%H%M%S).sql

# Verify backup integrity
mysql -u [username] -p -e "USE [database]; SELECT COUNT(*) FROM orders;"
```

## 📋 Migration Steps

### Phase 1: Database Preparation (30 minutes)

#### Step 1.1: Create Grand Total Column

```sql
-- Add the column with default value
ALTER TABLE orders ADD COLUMN grand_total DECIMAL(15,2) DEFAULT 0;

-- Verify column creation
DESCRIBE orders;
```

#### Step 1.2: Create Performance Indexes

```sql
-- Index for AccountManagerTarget queries
CREATE INDEX idx_orders_user_closing_grand
ON orders(user_id, closing_date, grand_total);

-- Index for reporting queries
CREATE INDEX idx_orders_grand_total
ON orders(grand_total);

-- Verify indexes
SHOW INDEX FROM orders WHERE Column_name = 'grand_total';
```

#### Step 1.3: Populate Existing Data

```bash
# Run the population command
php artisan orders:update-grand-totals --force

# Verify population success
php artisan tinker --execute="
\$total = \App\Models\Order::count();
\$populated = \App\Models\Order::where('grand_total', '>', 0)->count();
echo 'Total orders: ' . \$total . PHP_EOL;
echo 'Populated: ' . \$populated . PHP_EOL;
echo 'Success rate: ' . round((\$populated / \$total) * 100, 2) . '%' . PHP_EOL;
"
```

### Phase 2: Application Deployment (60 minutes)

#### Step 2.1: Deploy Code Changes

```bash
# Switch to maintenance mode
php artisan down --message="System maintenance in progress"

# Pull latest code
git pull origin main

# Update dependencies
composer install --no-dev --optimize-autoloader

# Clear and rebuild caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Step 2.2: Verify Deployment

```bash
# Test basic functionality
php artisan tinker --execute="
\$order = \App\Models\Order::first();
echo 'Grand total: ' . \$order->grand_total . PHP_EOL;
"

# Test model events
php artisan tinker --execute="
\$order = \App\Models\Order::first();
\$oldTotal = \$order->grand_total;
\$order->promo = (\$order->promo ?? 0) + 100000;
\$order->save();
echo 'Grand total updated: ' . (\$order->grand_total == \$oldTotal - 100000 ? 'SUCCESS' : 'FAILED') . PHP_EOL;
"
```

### Phase 3: AccountManager Target Update (30 minutes)

#### Step 3.1: Update Target Calculations

```bash
# Update all AccountManagerTarget records
php artisan targets:generate --update

# Verify calculations
php artisan tinker --execute="
\$sample = \App\Models\AccountManagerTarget::with('user')->take(3)->get();
foreach (\$sample as \$target) {
    echo \$target->user->name . ': IDR ' . number_format(\$target->achieved_amount) . PHP_EOL;
}
"
```

### Phase 4: Final Verification (30 minutes)

#### Step 4.1: Data Integrity Check

```bash
# Check for calculation errors
php artisan tinker --execute="
\$errors = \App\Models\Order::whereRaw(
    'grand_total != (total_price + COALESCE(penambahan, 0) - COALESCE(promo, 0) - COALESCE(pengurangan, 0))'
)->count();
echo 'Calculation errors: ' . \$errors . PHP_EOL;

\$nulls = \App\Models\Order::whereNull('grand_total')->count();
echo 'Null grand_totals: ' . \$nulls . PHP_EOL;
"
```

#### Step 4.2: Performance Verification

```bash
# Test query performance
php artisan tinker --execute="
\$start = microtime(true);
\$sum = \App\Models\Order::sum('grand_total');
\$end = microtime(true);
echo 'Total grand_total: IDR ' . number_format(\$sum) . PHP_EOL;
echo 'Query time: ' . round((\$end - \$start) * 1000, 2) . 'ms' . PHP_EOL;
"
```

#### Step 4.3: UI Testing

```bash
# Exit maintenance mode
php artisan up

# Manual testing checklist:
# [ ] Access admin panel successfully
# [ ] View order list page
# [ ] Create new order
# [ ] Edit existing order
# [ ] View AccountManagerTarget page
```

## 🔍 Validation Procedures

### Automated Validation

```bash
# Run comprehensive validation
php artisan tinker --execute="
// Test 1: Data completeness
\$totalOrders = \App\Models\Order::count();
\$ordersWithGrandTotal = \App\Models\Order::whereNotNull('grand_total')->where('grand_total', '>', 0)->count();
echo 'Test 1 - Data Completeness: ' . (\$ordersWithGrandTotal == \$totalOrders ? 'PASS' : 'FAIL') . PHP_EOL;

// Test 2: Calculation accuracy (sample)
\$sampleOrder = \App\Models\Order::inRandomOrder()->first();
\$calculated = \$sampleOrder->total_price + \$sampleOrder->penambahan - \$sampleOrder->promo - \$sampleOrder->pengurangan;
echo 'Test 2 - Calculation Accuracy: ' . (\$sampleOrder->grand_total == \$calculated ? 'PASS' : 'FAIL') . PHP_EOL;

// Test 3: Performance benchmark
\$start = microtime(true);
\App\Models\AccountManagerTarget::with('user')->get();
\$end = microtime(true);
\$time = (\$end - \$start) * 1000;
echo 'Test 3 - Performance: ' . (\$time < 500 ? 'PASS' : 'FAIL') . ' (' . round(\$time, 2) . 'ms)' . PHP_EOL;
"
```

### Manual Validation

1. **Admin Panel Access**

    - [ ] Login to admin panel
    - [ ] Navigate to Orders list
    - [ ] Verify grand_total column displays correctly

2. **Order Management**

    - [ ] Create new order with various pricing scenarios
    - [ ] Edit existing order and verify grand_total updates
    - [ ] Test form validation and error handling

3. **Reporting Functions**
    - [ ] View AccountManagerTarget resource
    - [ ] Check target achievement calculations
    - [ ] Verify performance improvement

## 🚨 Rollback Procedures

### Level 1: Quick Fix (Minor Issues)

```bash
# Recalculate all grand_totals
php artisan orders:update-grand-totals --force

# Refresh AccountManager targets
php artisan targets:generate --update
```

### Level 2: Code Rollback (Application Issues)

```bash
# Enable maintenance mode
php artisan down

# Revert to previous commit
git revert [commit-hash]

# Clear caches
php artisan optimize:clear

# Test basic functionality
php artisan tinker --execute="echo 'App functional: ' . (\App\Models\Order::count() > 0 ? 'YES' : 'NO');"

# Exit maintenance mode
php artisan up
```

### Level 3: Full Rollback (Critical Issues)

```bash
# Enable maintenance mode
php artisan down --message="Emergency maintenance - restoring from backup"

# Restore database
mysql -u [username] -p [database] < backup_YYYYMMDD_HHMMSS.sql

# Revert code changes
git reset --hard [previous-stable-commit]

# Clear all caches
php artisan optimize:clear

# Verify rollback
php artisan tinker --execute="echo 'Orders count: ' . \App\Models\Order::count();"

# Exit maintenance mode
php artisan up
```

## 📊 Success Metrics

### Technical Metrics

-   [ ] 100% of orders have populated `grand_total`
-   [ ] 0 calculation errors detected
-   [ ] Query performance improved by >50%
-   [ ] All tests passing

### Business Metrics

-   [ ] AccountManager reports load faster
-   [ ] No user complaints about performance
-   [ ] All financial calculations accurate
-   [ ] System availability >99.9%

## 📝 Migration Log Template

```
=== GRAND TOTAL MIGRATION LOG ===

Migration Date: [DATE]
Migration Team: [NAMES]
Start Time: [TIME]
End Time: [TIME]

PHASE 1 - DATABASE PREPARATION
□ Backup completed: [TIME] - [STATUS]
□ Column added: [TIME] - [STATUS]
□ Indexes created: [TIME] - [STATUS]
□ Data populated: [TIME] - [STATUS]
   - Orders processed: [COUNT]
   - Success rate: [PERCENTAGE]

PHASE 2 - APPLICATION DEPLOYMENT
□ Maintenance mode enabled: [TIME]
□ Code deployed: [TIME] - [STATUS]
□ Dependencies updated: [TIME] - [STATUS]
□ Caches rebuilt: [TIME] - [STATUS]
□ Functionality verified: [TIME] - [STATUS]

PHASE 3 - ACCOUNTMANAGER UPDATE
□ Targets updated: [TIME] - [STATUS]
   - Targets processed: [COUNT]
   - Success rate: [PERCENTAGE]

PHASE 4 - FINAL VERIFICATION
□ Data integrity: [STATUS]
□ Performance test: [STATUS] - [TIME]ms
□ UI testing: [STATUS]
□ Maintenance mode disabled: [TIME]

ISSUES ENCOUNTERED:
[List any issues and how they were resolved]

PERFORMANCE IMPROVEMENTS:
- AccountManagerTarget queries: [BEFORE]ms → [AFTER]ms
- Order calculations: [BEFORE]ms → [AFTER]ms

FINAL STATUS: [SUCCESS/FAILED]
NEXT STEPS: [Actions to take post-migration]

Signed off by:
Developer: [NAME] - [SIGNATURE]
DBA: [NAME] - [SIGNATURE]
```

## 🎯 Post-Migration Tasks

### Immediate (Within 24 hours)

-   [ ] Monitor system performance
-   [ ] Check error logs for issues
-   [ ] Verify user feedback
-   [ ] Run additional data integrity checks

### Short-term (Within 1 week)

-   [ ] Performance analysis and optimization
-   [ ] User training on any UI changes
-   [ ] Documentation updates
-   [ ] Backup strategy review

### Long-term (Within 1 month)

-   [ ] Performance trend analysis
-   [ ] Storage usage monitoring
-   [ ] Index optimization review
-   [ ] Migration retrospective meeting

---

**Emergency Contact**: [Phone] | [Email]  
**Escalation Path**: Developer → DBA → Technical Lead → CTO  
**Documentation Version**: 1.0 | August 26, 2025
