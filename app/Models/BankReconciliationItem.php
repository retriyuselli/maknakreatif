<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class BankReconciliationItem extends Model
{
    protected $fillable = [
        'bank_reconciliation_id',
        'date',
        'description',
        'debit',
        'credit',
        'row_number',
    ];

    protected $casts = [
        'date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    // Main relationship to BankStatement (new integrated approach)
    public function bankStatement(): BelongsTo
    {
        return $this->belongsTo(BankStatement::class, 'bank_reconciliation_id', 'id');
    }

    // Get the parent record - checks both BankStatement and legacy data
    public function getParentRecord()
    {
        // First try BankStatement (new integrated approach)
        $bankStatement = BankStatement::find($this->bank_reconciliation_id);
        if ($bankStatement) {
            return $bankStatement;
        }

        // Fallback to check if there's a bank_reconciliations table record (legacy data)
        try {
            $legacyRecord = DB::table('bank_reconciliations')
                ->where('id', $this->bank_reconciliation_id)
                ->first();
            
            if ($legacyRecord) {
                // Return a simple object for legacy compatibility
                return (object) [
                    'id' => $legacyRecord->id,
                    'title' => $legacyRecord->title ?? 'Legacy Record',
                    'description' => $legacyRecord->description ?? '',
                    'status' => $legacyRecord->status ?? 'completed',
                    'type' => 'legacy'
                ];
            }
        } catch (\Exception $e) {
            // If bank_reconciliations table doesn't exist, ignore
        }
        
        return null;
    }

    // Dynamic relationship - works with both new and legacy data
    public function parent()
    {
        return $this->getParentRecord();
    }
}
