<?php

namespace App\Services;

use App\Models\UnifiedTransaction;
use App\Models\BankReconciliationItem;
use App\Models\PaymentMethod;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReconciliationService
{
    const EXACT_MATCH_CONFIDENCE = 100.00;
    const HIGH_CONFIDENCE = 85.00;
    const MEDIUM_CONFIDENCE = 70.00;
    const LOW_CONFIDENCE = 50.00;

    /**
     * Perform reconciliation between PaymentMethod transactions and BankStatement items
     */
    public function reconcile(int $paymentMethodId, ?string $startDate = null, ?string $endDate = null): array
    {
        // Get unified transactions from PaymentMethod
        $appTransactions = UnifiedTransaction::getForPaymentMethod(
            $paymentMethodId, 
            $startDate, 
            $endDate
        );

        // Get bank statement items for the same PaymentMethod
        $bankItems = $this->getBankItemsForPaymentMethod($paymentMethodId, $startDate, $endDate);

        $results = [
            'matched' => [],
            'unmatched_app' => [],
            'unmatched_bank' => [],
            'disputed' => [],
            'statistics' => [
                'total_app_transactions' => $appTransactions->count(),
                'total_bank_items' => $bankItems->count(),
                'matched_count' => 0,
                'unmatched_app_count' => 0,
                'unmatched_bank_count' => 0,
                'disputed_count' => 0,
            ]
        ];

        $matchedBankIds = [];
        $matchedAppIds = [];

        // Phase 1: Exact matches (Date + Amount)
        foreach ($appTransactions as $appTx) {
            $exactMatch = $this->findExactMatch($appTx, $bankItems, $matchedBankIds);
            
            if ($exactMatch) {
                $results['matched'][] = [
                    'app_transaction' => $appTx,
                    'bank_item' => $exactMatch,
                    'confidence' => self::EXACT_MATCH_CONFIDENCE,
                    'match_type' => 'exact',
                    'match_criteria' => ['date', 'amount']
                ];

                $matchedBankIds[] = $exactMatch->id;
                $matchedAppIds[] = $this->getTransactionKey($appTx);
            }
        }

        // Phase 2: Fuzzy matches (Date range + Amount tolerance + Description similarity)
        foreach ($appTransactions as $appTx) {
            $txKey = $this->getTransactionKey($appTx);
            if (in_array($txKey, $matchedAppIds)) {
                continue; // Already matched
            }

            $fuzzyMatch = $this->findFuzzyMatch($appTx, $bankItems, $matchedBankIds);
            
            if ($fuzzyMatch['item'] && $fuzzyMatch['confidence'] >= self::LOW_CONFIDENCE) {
                $results['matched'][] = [
                    'app_transaction' => $appTx,
                    'bank_item' => $fuzzyMatch['item'],
                    'confidence' => $fuzzyMatch['confidence'],
                    'match_type' => 'fuzzy',
                    'match_criteria' => $fuzzyMatch['criteria']
                ];

                $matchedBankIds[] = $fuzzyMatch['item']->id;
                $matchedAppIds[] = $txKey;
            }
        }

        // Collect unmatched transactions
        foreach ($appTransactions as $appTx) {
            $txKey = $this->getTransactionKey($appTx);
            if (!in_array($txKey, $matchedAppIds)) {
                $results['unmatched_app'][] = $appTx;
            }
        }

        foreach ($bankItems as $bankItem) {
            if (!in_array($bankItem->id, $matchedBankIds)) {
                $results['unmatched_bank'][] = $bankItem;
            }
        }

        // Update statistics
        $results['statistics']['matched_count'] = count($results['matched']);
        $results['statistics']['unmatched_app_count'] = count($results['unmatched_app']);
        $results['statistics']['unmatched_bank_count'] = count($results['unmatched_bank']);
        
        // Calculate match percentage
        $totalAppTransactions = $results['statistics']['total_app_transactions'];
        $results['statistics']['match_percentage'] = $totalAppTransactions > 0 
            ? round(($results['statistics']['matched_count'] / $totalAppTransactions) * 100, 1) 
            : 0;
            
        // Calculate totals for export
        $results['statistics']['total_app_debit'] = $appTransactions->sum('debit_amount');
        $results['statistics']['total_app_credit'] = $appTransactions->sum('credit_amount');
        $results['statistics']['total_bank_debit'] = $bankItems->sum('debit');
        $results['statistics']['total_bank_credit'] = $bankItems->sum('credit');

        return $results;
    }

    /**
     * Find exact match (same date and amount)
     */
    private function findExactMatch($appTx, Collection $bankItems, array $excludeIds)
    {
        return $bankItems->first(function ($bankItem) use ($appTx, $excludeIds) {
            if (in_array($bankItem->id, $excludeIds)) {
                return false;
            }

            // Check date match
            $dateMatch = $appTx->transaction_date->isSameDay($bankItem->date);

            // Check amount match
            $amountMatch = false;
            if ($appTx->is_income) {
                // App transaction is income (credit), should match bank credit
                $amountMatch = abs($appTx->credit_amount - $bankItem->credit) < 0.01;
            } else {
                // App transaction is expense (debit), should match bank debit
                $amountMatch = abs($appTx->debit_amount - $bankItem->debit) < 0.01;
            }

            return $dateMatch && $amountMatch;
        });
    }

    /**
     * Find fuzzy match with confidence scoring
     */
    private function findFuzzyMatch($appTx, Collection $bankItems, array $excludeIds): array
    {
        $bestMatch = null;
        $bestConfidence = 0;
        $bestCriteria = [];

        foreach ($bankItems as $bankItem) {
            if (in_array($bankItem->id, $excludeIds)) {
                continue;
            }

            $confidence = 0;
            $criteria = [];

            // Date proximity (max 3 days tolerance)
            $daysDiff = abs($appTx->transaction_date->diffInDays($bankItem->date));
            if ($daysDiff == 0) {
                $confidence += 40; // Same day
                $criteria[] = 'same_date';
            } elseif ($daysDiff <= 1) {
                $confidence += 30; // 1 day difference
                $criteria[] = 'close_date';
            } elseif ($daysDiff <= 3) {
                $confidence += 15; // 2-3 days difference
                $criteria[] = 'near_date';
            } else {
                continue; // Too far apart
            }

            // Amount match
            $amountMatch = false;
            if ($appTx->is_income) {
                $amountDiff = abs($appTx->credit_amount - $bankItem->credit);
                if ($amountDiff < 0.01) {
                    $confidence += 40; // Exact amount
                    $criteria[] = 'exact_amount';
                    $amountMatch = true;
                } elseif ($amountDiff / $appTx->credit_amount <= 0.02) {
                    $confidence += 25; // Within 2%
                    $criteria[] = 'close_amount';
                    $amountMatch = true;
                }
            } else {
                $amountDiff = abs($appTx->debit_amount - $bankItem->debit);
                if ($amountDiff < 0.01) {
                    $confidence += 40; // Exact amount
                    $criteria[] = 'exact_amount';
                    $amountMatch = true;
                } elseif ($amountDiff / $appTx->debit_amount <= 0.02) {
                    $confidence += 25; // Within 2%
                    $criteria[] = 'close_amount';
                    $amountMatch = true;
                }
            }

            if (!$amountMatch) {
                continue; // Amount too different
            }

            // Description similarity (using Levenshtein distance)
            $similarity = $this->calculateDescriptionSimilarity(
                $appTx->description,
                $bankItem->description
            );

            if ($similarity > 0.8) {
                $confidence += 20; // High similarity
                $criteria[] = 'high_desc_similarity';
            } elseif ($similarity > 0.6) {
                $confidence += 10; // Medium similarity
                $criteria[] = 'medium_desc_similarity';
            } elseif ($similarity > 0.4) {
                $confidence += 5; // Low similarity
                $criteria[] = 'low_desc_similarity';
            }

            if ($confidence > $bestConfidence) {
                $bestConfidence = $confidence;
                $bestMatch = $bankItem;
                $bestCriteria = $criteria;
            }
        }

        return [
            'item' => $bestMatch,
            'confidence' => $bestConfidence,
            'criteria' => $bestCriteria
        ];
    }

    /**
     * Calculate description similarity using Levenshtein distance
     */
    private function calculateDescriptionSimilarity(string $desc1, string $desc2): float
    {
        $desc1 = strtolower(trim($desc1));
        $desc2 = strtolower(trim($desc2));

        if (empty($desc1) || empty($desc2)) {
            return 0.0;
        }

        $maxLength = max(strlen($desc1), strlen($desc2));
        if ($maxLength == 0) {
            return 1.0;
        }

        $distance = levenshtein($desc1, $desc2);
        return 1 - ($distance / $maxLength);
    }

    /**
     * Get bank reconciliation items for specific PaymentMethod
     */
    private function getBankItemsForPaymentMethod(int $paymentMethodId, ?string $startDate, ?string $endDate): Collection
    {
        // Get BankStatements for this PaymentMethod
        $bankStatements = \App\Models\BankStatement::where('payment_method_id', $paymentMethodId)
            ->when($startDate, fn($q) => $q->where('period_start', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('period_end', '<=', $endDate))
            ->pluck('id');

        // Get all BankReconciliationItems for these statements
        return BankReconciliationItem::whereIn('bank_reconciliation_id', $bankStatements)
            ->when($startDate, fn($q) => $q->where('date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('date', '<=', $endDate))
            ->orderBy('date')
            ->get();
    }

    /**
     * Generate unique key for app transaction
     */
    private function getTransactionKey($appTx): string
    {
        return $appTx->source_table . '_' . $appTx->source_id;
    }

    /**
     * Mark transactions as matched in database
     */
    public function markAsMatched($appTransaction, $bankItem, float $confidence, array $criteria): void
    {
        // Update the source transaction record
        $model = $this->getSourceModel($appTransaction);
        if ($model) {
            $model->update([
                'reconciliation_status' => 'matched',
                'matched_bank_item_id' => $bankItem->id,
                'match_confidence' => $confidence,
                'reconciliation_notes' => 'Auto-matched: ' . implode(', ', $criteria)
            ]);
        }
    }

    /**
     * Get source model instance for app transaction
     */
    private function getSourceModel($appTransaction)
    {
        switch ($appTransaction->source_table) {
            case 'data_pembayarans':
                return \App\Models\DataPembayaran::find($appTransaction->source_id);
            case 'pendapatan_lains':
                return \App\Models\PendapatanLain::find($appTransaction->source_id);
            case 'expenses':
                return \App\Models\Expense::find($appTransaction->source_id);
            case 'expense_ops':
                return \App\Models\ExpenseOps::find($appTransaction->source_id);
            case 'pengeluaran_lains':
                return \App\Models\PengeluaranLain::find($appTransaction->source_id);
            default:
                return null;
        }
    }
}
