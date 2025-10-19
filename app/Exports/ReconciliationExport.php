<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReconciliationExport implements WithMultipleSheets
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Summary Sheet
        $sheets[] = new ReconciliationSummarySheet($this->data);

        // Matched Transactions Sheet
        $sheets[] = new ReconciliationMatchedSheet($this->data['matched']);

        // Unmatched App Transactions Sheet
        $sheets[] = new ReconciliationUnmatchedAppSheet($this->data['unmatched_app']);

        // Unmatched Bank Transactions Sheet
        $sheets[] = new ReconciliationUnmatchedBankSheet($this->data['unmatched_bank']);

        return $sheets;
    }
}

class ReconciliationSummarySheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function title(): string
    {
        return 'Ringkasan';
    }

    public function headings(): array
    {
        return [
            'Keterangan',
            'Nilai'
        ];
    }

    public function array(): array
    {
        $statistics = $this->data['statistics'];
        $paymentMethod = $this->data['payment_method'];
        $period = $this->data['period'];

        return [
            ['Bank', $paymentMethod->bank_name],
            ['No. Rekening', $paymentMethod->no_rekening],
            ['Periode Mulai', $period['start']],
            ['Periode Akhir', $period['end']],
            ['', ''],
            ['STATISTIK REKONSILIASI', ''],
            ['Total Transaksi Aplikasi', $statistics['total_app_transactions']],
            ['Total Item Bank', $statistics['total_bank_items']],
            ['Total Matched', $statistics['matched_count']],
            ['Persentase Match', $statistics['match_percentage'] . '%'],
            ['', ''],
            ['NOMINAL (IDR)', ''],
            ['Total Debit Aplikasi', number_format($statistics['total_app_debit'], 0, ',', '.')],
            ['Total Credit Aplikasi', number_format($statistics['total_app_credit'], 0, ',', '.')],
            ['Total Debit Bank', number_format($statistics['total_bank_debit'], 0, ',', '.')],
            ['Total Credit Bank', number_format($statistics['total_bank_credit'], 0, ',', '.')],
            ['', ''],
            ['Selisih Debit', number_format($statistics['total_app_debit'] - $statistics['total_bank_debit'], 0, ',', '.')],
            ['Selisih Credit', number_format($statistics['total_app_credit'] - $statistics['total_bank_credit'], 0, ',', '.')],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            6 => ['font' => ['bold' => true]],
            12 => ['font' => ['bold' => true]],
        ];
    }
}

class ReconciliationMatchedSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected $matched;

    public function __construct($matched)
    {
        $this->matched = $matched;
    }

    public function title(): string
    {
        return 'Transaksi Matched';
    }

    public function headings(): array
    {
        return [
            'Tanggal App',
            'Keterangan App',
            'Debit App',
            'Credit App',
            'Tanggal Bank',
            'Keterangan Bank',
            'Debit Bank',
            'Credit Bank',
            'Confidence',
            'Tipe Match'
        ];
    }

    public function array(): array
    {
        $data = [];
        
        foreach ($this->matched as $match) {
            $appTransaction = $match['app_transaction'];
            $bankItem = $match['bank_item'];
            
            $data[] = [
                $appTransaction->transaction_date,
                $appTransaction->description,
                $appTransaction->debit_amount ? number_format($appTransaction->debit_amount, 0, ',', '.') : '',
                $appTransaction->credit_amount ? number_format($appTransaction->credit_amount, 0, ',', '.') : '',
                $bankItem->transaction_date,
                $bankItem->description,
                $bankItem->debit_amount ? number_format($bankItem->debit_amount, 0, ',', '.') : '',
                $bankItem->credit_amount ? number_format($bankItem->credit_amount, 0, ',', '.') : '',
                $match['confidence'] . '%',
                isset($match['match_criteria']) ? implode(', ', $match['match_criteria']) : (isset($match['match_reasons']) ? implode(', ', $match['match_reasons']) : 'N/A')
            ];
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

class ReconciliationUnmatchedAppSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected $unmatched;

    public function __construct($unmatched)
    {
        $this->unmatched = $unmatched;
    }

    public function title(): string
    {
        return 'App Unmatched';
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Keterangan',
            'Debit',
            'Credit',
            'Tipe Transaksi',
            'Source ID'
        ];
    }

    public function array(): array
    {
        $data = [];
        
        foreach ($this->unmatched as $transaction) {
            $data[] = [
                $transaction->transaction_date,
                $transaction->description,
                $transaction->debit_amount ? number_format($transaction->debit_amount, 0, ',', '.') : '',
                $transaction->credit_amount ? number_format($transaction->credit_amount, 0, ',', '.') : '',
                $transaction->source_table,
                $transaction->source_id
            ];
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

class ReconciliationUnmatchedBankSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected $unmatched;

    public function __construct($unmatched)
    {
        $this->unmatched = $unmatched;
    }

    public function title(): string
    {
        return 'Bank Unmatched';
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Keterangan',
            'Debit',
            'Credit',
            'Bank Item ID'
        ];
    }

    public function array(): array
    {
        $data = [];
        
        foreach ($this->unmatched as $item) {
            $data[] = [
                $item->transaction_date,
                $item->description,
                $item->debit_amount ? number_format($item->debit_amount, 0, ',', '.') : '',
                $item->credit_amount ? number_format($item->credit_amount, 0, ',', '.') : '',
                $item->id
            ];
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
