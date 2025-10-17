<?php

namespace App\Http\Controllers;

use App\Models\BankStatement;
use App\Services\ReconciliationService;
use Illuminate\Http\Request;

class BankReconciliationPageController extends Controller
{
    public function show(BankStatement $bankStatement)
    {
        // Verify that the record has payment method and reconciliation items
        if (!$bankStatement->payment_method_id || !$bankStatement->paymentMethod) {
            abort(404, 'Bank statement tidak memiliki payment method yang valid.');
        }

        if ($bankStatement->reconciliationItems()->count() === 0) {
            abort(404, 'Bank statement tidak memiliki data reconciliation items.');
        }

        // Load relationships
        $bankStatement->load('paymentMethod', 'reconciliationItems');

        // Get reconciliation results
        $reconciliationService = new ReconciliationService();
        $reconciliationResults = $reconciliationService->reconcile(
            $bankStatement->payment_method_id,
            $bankStatement->period_start->format('Y-m-d'),
            $bankStatement->period_end->format('Y-m-d')
        );

        return view('bank-reconciliation.comparison', [
            'record' => $bankStatement,
            'reconciliationResults' => $reconciliationResults,
            'statistics' => $reconciliationResults['statistics']
        ]);
    }
}
