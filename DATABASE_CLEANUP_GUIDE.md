# Database Cleanup Guide - Bank Reconciliation Spacing

## Problem
While local environment shows proper spacing after code fixes, server environment still displays excessive whitespace in description fields due to existing database data containing multiple spaces, tabs, and newlines.

## Solution
Use the custom Artisan command to clean existing database records.

## Commands

### 1. Test First (Dry Run)
```bash
cd /home/u380354370/domains/maknakreatif.id/public_html
php artisan bank:cleanup-spacing --dry-run
```

This will show you what would be changed without making any actual changes.

### 2. Verbose Dry Run (See Details)
```bash
php artisan bank:cleanup-spacing --dry-run -v
```

This will show you exactly which records would be changed and how.

### 3. Execute Cleanup
```bash
php artisan bank:cleanup-spacing
```

This will actually update the database records.

## What It Does

The command:
1. Loads all BankReconciliationItem records
2. Applies `preg_replace('/\s+/', ' ', trim($description))` to each description
3. Only updates records that actually change
4. Shows progress bar and summary
5. Supports dry-run mode for safety

## Expected Results

After running the cleanup:
- Multiple spaces become single spaces
- Tabs become single spaces
- Newlines become single spaces
- Leading/trailing whitespace is removed
- Server environment will match local environment behavior

## Server Deployment Steps

1. **Upload the new files:**
   ```bash
   git pull origin main
   ```

2. **Clear caches:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

3. **Test the command:**
   ```bash
   php artisan bank:cleanup-spacing --dry-run -v
   ```

4. **Run the cleanup:**
   ```bash
   php artisan bank:cleanup-spacing
   ```

5. **Verify results:**
   - Log into admin panel
   - Check bank reconciliation items
   - Verify spacing is now consistent

## Safety Features

- **Dry run mode**: Test before applying changes
- **Progress tracking**: Shows which records are being processed
- **Verbose output**: See exactly what changes are being made
- **Only updates changed records**: Doesn't touch records that are already clean
- **Transaction safety**: Each save() is atomic

## Troubleshooting

If spacing issues persist after cleanup:
1. Check browser cache - hard refresh (Ctrl+F5 / Cmd+Shift+R)
2. Verify command completed successfully
3. Check if new records are being created with proper formatting
4. Verify the formatStateUsing/dehydrateStateUsing functions are active in the code