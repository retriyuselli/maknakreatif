<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReconciliationService;
use App\Models\UnifiedTransaction;
use Illuminate\Support\Facades\DB;

class ReconciliationController extends Controller
{
    protected $reconciliationService;

    public function __construct()
    {
        $this->reconciliationService = new ReconciliationService();
    }

    /**
     * Mark individual transaction as matched
     */
    public function markMatched(Request $request)
    {
        $request->validate([
            'source_id' => 'required|integer',
            'source_table' => 'required|string',
            'bank_item_id' => 'required|integer',
            'confidence' => 'required|numeric'
        ]);

        try {
            // Create a mock transaction object for the service
            $mockTransaction = (object) [
                'source_table' => $request->source_table,
                'source_id' => $request->source_id
            ];

            // Get bank item
            $bankItem = \App\Models\BankReconciliationItem::findOrFail($request->bank_item_id);

            // Mark as matched
            $this->reconciliationService->markAsMatched(
                $mockTransaction,
                $bankItem,
                $request->confidence,
                ['manual_match']
            );

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil ditandai sebagai cocok'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto match high confidence transactions
     */
    public function autoMatch(Request $request)
    {
        $request->validate([
            'payment_method_id' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ]);

        try {
            $results = $this->reconciliationService->reconcile(
                $request->payment_method_id,
                $request->start_date,
                $request->end_date
            );

            $matchedCount = 0;

            foreach ($results['matched'] as $match) {
                if ($match['confidence'] >= ReconciliationService::HIGH_CONFIDENCE) {
                    $this->reconciliationService->markAsMatched(
                        $match['app_transaction'],
                        $match['bank_item'],
                        $match['confidence'],
                        $match['match_criteria']
                    );
                    $matchedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'matched_count' => $matchedCount,
                'message' => "$matchedCount transaksi berhasil di-match otomatis"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan auto match: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export reconciliation results to Excel
     */
    public function export(Request $request)
    {
        $request->validate([
            'payment_method_id' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ]);

        try {
            $results = $this->reconciliationService->reconcile(
                $request->payment_method_id,
                $request->start_date,
                $request->end_date
            );

            // Get payment method for filename
            $paymentMethod = \App\Models\PaymentMethod::findOrFail($request->payment_method_id);
            $filename = 'reconciliation_' . str_replace([' ', '-', '/'], '_', $paymentMethod->no_rekening) . '_' . 
                       $request->start_date . '_to_' . $request->end_date . '.xlsx';

            // Prepare export data
            $exportData = [
                'matched' => $results['matched'],
                'unmatched_app' => $results['unmatched_app'],
                'unmatched_bank' => $results['unmatched_bank'],
                'statistics' => $results['statistics'],
                'payment_method' => $paymentMethod,
                'period' => [
                    'start' => $request->start_date,
                    'end' => $request->end_date
                ]
            ];

            // Use Maatwebsite Excel package for proper Excel export
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\ReconciliationExport($exportData), 
                $filename
            );

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal export data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unmark matched transaction
     */
    public function unmarkMatched(Request $request)
    {
        $request->validate([
            'source_id' => 'required|integer',
            'source_table' => 'required|string',
            'bank_item_id' => 'required|integer'
        ]);

        try {
            // Reset reconciliation status in source table
            $table = $request->source_table;
            DB::table($table)
                ->where('id', $request->source_id)
                ->update([
                    'reconciliation_status' => 'uploaded',
                    'matched_bank_item_id' => null,
                    'match_confidence' => null,
                    'updated_at' => now()
                ]);

            // Reset bank item as well if needed
            \App\Models\BankReconciliationItem::where('id', $request->bank_item_id)
                ->update(['is_matched' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Match berhasil dibatalkan'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan match: ' . $e->getMessage()
            ], 500);
        }
    }
}
