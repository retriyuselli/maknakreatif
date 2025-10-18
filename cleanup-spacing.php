<?php

// Script untuk membersihkan spasi berlebihan di database server
// Jalankan sekali saja: php artisan tinker < cleanup-spacing.php

use App\Models\BankReconciliationItem;

echo "Starting cleanup of excessive whitespace in bank reconciliation items...\n";

$items = BankReconciliationItem::all();
$updated = 0;

foreach ($items as $item) {
    $originalDescription = $item->description;
    
    // Clean excessive whitespace
    $cleanDescription = preg_replace('/\s+/', ' ', trim($originalDescription));
    
    if ($originalDescription !== $cleanDescription) {
        $item->description = $cleanDescription;
        $item->save();
        $updated++;
        
        echo "Updated item ID {$item->id}: \n";
        echo "  Before: " . json_encode($originalDescription) . "\n";
        echo "  After:  " . json_encode($cleanDescription) . "\n\n";
    }
}

echo "Cleanup completed! Updated {$updated} items.\n";
