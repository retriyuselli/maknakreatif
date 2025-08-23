<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\DataPembayaran; // Pastikan path model ini benar
use App\Models\Expense;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf; // Import PDF Facade
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function generateDataPembayaranHtmlReport(Request $request): View
    {
        $selectedMonth = $request->input('month');
        $selectedYear = $request->input('year');
        $selectedStatus = $request->input('status'); // Ambil dari request

        // Data untuk dropdown filter
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[str_pad($m, 2, '0', STR_PAD_LEFT)] = Carbon::create()->month($m)->locale('id')->isoFormat('MMMM');
        }

        // Ambil tahun unik dari data pembayaran yang ada, atau default ke rentang beberapa tahun
        $availableYearsDb = DataPembayaran::selectRaw('DISTINCT YEAR(tgl_bayar) as year')
                                      ->whereNotNull('tgl_bayar')
                                      ->orderBy('year', 'desc')
                                      ->pluck('year')
                                      ->filter() // Menghilangkan nilai null jika ada
                                      ->toArray();
        
        $currentYear = (int)date('Y');
        $defaultYears = range($currentYear, $currentYear - 5);
        $years = !empty($availableYearsDb) ? $availableYearsDb : $defaultYears;
        if (!empty($availableYearsDb) && $selectedYear && !in_array($selectedYear, $years)) {
            // Jika tahun yang dipilih tidak ada di daftar tahun dari DB (misal dari input manual URL), tambahkan
             array_push($years, (int)$selectedYear);
             sort($years, SORT_NUMERIC);
             $years = array_reverse($years);
        }

        $orderStatuses = OrderStatus::cases();
        
        $query = DataPembayaran::query()->with(['order', 'paymentMethod']); // Load order, status is an attribute

        if ($selectedMonth && $selectedYear) {
            $query->whereYear('tgl_bayar', $selectedYear)
                ->whereMonth('tgl_bayar', $selectedMonth);
        } elseif ($selectedYear) {
            $query->whereYear('tgl_bayar', $selectedYear);
        }

        // Tambahkan filter status
        if ($selectedStatus) {
            $query->whereHas('order', function ($q) use ($selectedStatus) {
                $q->where('status', $selectedStatus); // Asumsi kolom status di tabel order
            });
        }

        $dataPembayarans = $query->orderBy('tgl_bayar', 'desc')->get();

        return view('reports.pembayaran_html', compact(
            'dataPembayarans',
            'months',
            'years',
            'selectedMonth',
            'selectedYear',
            'orderStatuses',
            'selectedStatus'             
        ));
    }

    public function generateExpenseOpsHtmlReport(Request $request): View
    {
        $query = \App\Models\ExpenseOps::query(); // Using fully qualified name to avoid ambiguity

        $selectedMonth = $request->input('month');
        $selectedYear = $request->input('year');
        $searchName = $request->input('search_name');
        $searchNote = $request->input('search_note');

        if ($selectedYear) {
            $query->whereYear('date_expense', $selectedYear);
            if ($selectedMonth) {
                $query->whereMonth('date_expense', $selectedMonth);
            }
        }

        if ($searchName) {
            $query->where('name', 'like', '%' . $searchName . '%');
        }

        if ($searchNote) {
            // Jika kolom catatan Anda adalah 'note'
            // Jika nama kolomnya berbeda, sesuaikan 'note' di bawah ini
            $query->where('note', 'like', '%' . $searchNote . '%');
        }

        $expenseOps = $query->orderBy('date_expense', 'desc')->get();

        // Data untuk dropdown filter
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[str_pad($m, 2, '0', STR_PAD_LEFT)] = Carbon::create()->month($m)->locale('id')->isoFormat('MMMM');
        }

        // Ambil tahun unik dari data pengeluaran operasional yang ada
        $availableYearsDb = \App\Models\ExpenseOps::selectRaw('DISTINCT YEAR(date_expense) as year')
                                           ->whereNotNull('date_expense')
                                           ->orderBy('year', 'desc')
                                           ->pluck('year')
                                           ->filter()
                                           ->toArray();

        $currentYear = (int)date('Y');
        $defaultYears = range($currentYear, $currentYear - 5);
        $years = !empty($availableYearsDb) ? $availableYearsDb : $defaultYears;

        // Pastikan tahun yang dipilih ada dalam daftar tahun yang tersedia
        if (!empty($availableYearsDb) && $selectedYear && !in_array($selectedYear, $years)) {
            array_push($years, (int)$selectedYear);
            rsort($years); // Urutkan descending (terbaru ke terlama)
        }

        return view('reports.expense_ops_html', compact(
            'expenseOps',
            'months',
            'years',
            'selectedMonth',
            'selectedYear',
            'searchName',
            'searchNote'
        ));
    }

    public function generateExpenseOpsPdfReport(Request $request)
    {
        $query = \App\Models\ExpenseOps::query();

        $selectedMonth = $request->input('month');
        $selectedYear = $request->input('year');
        $searchName = $request->input('search_name');
        $searchNote = $request->input('search_note');

        if ($selectedYear) {
            $query->whereYear('date_expense', $selectedYear);
            if ($selectedMonth) {
                $query->whereMonth('date_expense', $selectedMonth);
            }
        }

        if ($searchName) {
            $query->where('name', 'like', '%' . $searchName . '%');
        }

        if ($searchNote) {
            $query->where('note', 'like', '%' . $searchNote . '%');
        }

        $expenseOps = $query->orderBy('date_expense', 'desc')->get();

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[str_pad($m, 2, '0', STR_PAD_LEFT)] = Carbon::create()->month($m)->locale('id')->isoFormat('MMMM');
        }

        $availableYearsDb = \App\Models\ExpenseOps::selectRaw('DISTINCT YEAR(date_expense) as year')
                                           ->whereNotNull('date_expense')
                                           ->orderBy('year', 'desc')
                                           ->pluck('year')
                                           ->filter()
                                           ->toArray();
        $currentYear = (int)date('Y');
        $defaultYears = range($currentYear, $currentYear - 5);
        $years = !empty($availableYearsDb) ? $availableYearsDb : $defaultYears;
        if (!empty($availableYearsDb) && $selectedYear && !in_array($selectedYear, $years)) {
            array_push($years, (int)$selectedYear);
            rsort($years);
        }

        $data = compact('expenseOps', 'months', 'years', 'selectedMonth', 'selectedYear', 'searchName', 'searchNote');
        $pdf = Pdf::loadView('pdf.expense_ops_report_pdf', $data); // Menggunakan view PDF baru
        return $pdf->download('laporan-pengeluaran-operasional.pdf');
    }

    //Rute untuk laporan pengeluaran (expense wedding)
    public function generateExpenseHtmlReport(Request $request): View
    {
        $query = \App\Models\Expense::query(); // Menggunakan model Expense

        $selectedMonth = $request->input('month');
        $selectedYear = $request->input('year');
        $searchName = $request->input('search_name'); // Asumsi nama pengeluaran ada di kolom 'name'
        $searchNote = $request->input('search_note'); // Asumsi catatan ada di kolom 'note'
        $selectedOrderStatus = $request->input('order_status');
        $query = Expense::query()->with(['order', 'vendor']); // Pastikan relasi order di-load

        if ($selectedYear) {
            $query->whereYear('date_expense', $selectedYear);
            if ($selectedMonth) {
                $query->whereMonth('date_expense', $selectedMonth);
            }
        }

        if ($searchName) {
            $query->whereHas('order', function ($q) use ($searchName) {
                $q->where('name', 'like', '%' . $searchName . '%');
            });
        }

        if ($searchNote) {
            $query->where('note', 'like', '%' . $searchNote . '%');
        }

        if ($selectedOrderStatus) {
            $query->whereHas('order', function ($q) use ($selectedOrderStatus) {
                $q->where('status', $selectedOrderStatus);
            });
        }

        $expenses = $query->orderBy('date_expense', 'desc')->get();
        $expensesWedding = $query->get();
        $orderStatuses = \App\Enums\OrderStatus::cases();

        // Data untuk dropdown filter
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[str_pad($m, 2, '0', STR_PAD_LEFT)] = Carbon::create()->month($m)->locale('id')->isoFormat('MMMM');
        }

        $availableYearsDb = \App\Models\Expense::selectRaw('DISTINCT YEAR(date_expense) as year')
                                           ->whereNotNull('date_expense')
                                           ->orderBy('year', 'desc')
                                           ->pluck('year')
                                           ->filter()
                                           ->toArray();
        $currentYear = (int)date('Y');
        $defaultYears = range($currentYear, $currentYear - 5);
        $years = !empty($availableYearsDb) ? $availableYearsDb : $defaultYears;
        if (!empty($availableYearsDb) && $selectedYear && !in_array($selectedYear, $years)) {
            array_push($years, (int)$selectedYear);
            rsort($years);
        }

        return view('reports.expense_html', compact(
            'expenses', 
            'months', 
            'years', 
            'selectedMonth', 
            'selectedYear', 
            'searchName', 
            'searchNote',
            'orderStatuses',
            'selectedOrderStatus',
            'expensesWedding'
        ));
    }

    public function generateExpensePdfReport(Request $request)
    {
        $query = \App\Models\Expense::query(); // Menggunakan model Expense

        $selectedMonth = $request->input('month');
        $selectedYear = $request->input('year');
        $searchName = $request->input('search_name'); // Ini akan mencari nama dari relasi order
        $searchNote = $request->input('search_note');
        $selectedOrderStatus = $request->input('order_status'); // Ambil status order dari request

        if ($selectedYear) {
            $query->whereYear('date_expense', $selectedYear);
            if ($selectedMonth) {
                $query->whereMonth('date_expense', $selectedMonth);
            }
        }

        if ($searchName) {
            $query->whereHas('order', function ($q) use ($searchName) {
                $q->where('name', 'like', '%' . $searchName . '%');
            });
        }

        if ($searchNote) {
            $query->where('note', 'like', '%' . $searchNote . '%');
        }

        // Tambahkan filter status order jika ada
        if ($selectedOrderStatus) {
            $query->whereHas('order', function ($q) use ($selectedOrderStatus) {
                $q->where('status', $selectedOrderStatus);
            });
        }

        $expenses = $query->orderBy('date_expense', 'desc')->get();
        $orderStatuses = \App\Enums\OrderStatus::cases(); // Ambil semua kasus status order

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[str_pad($m, 2, '0', STR_PAD_LEFT)] = Carbon::create()->month($m)->locale('id')->isoFormat('MMMM');
        }

        $availableYearsDb = \App\Models\Expense::selectRaw('DISTINCT YEAR(date_expense) as year')
                                           ->whereNotNull('date_expense')
                                           ->orderBy('year', 'desc')
                                           ->pluck('year')
                                           ->filter()
                                           ->toArray();
        $currentYear = (int)date('Y');
        $defaultYears = range($currentYear, $currentYear - 5);
        $years = !empty($availableYearsDb) ? $availableYearsDb : $defaultYears;
        if (!empty($availableYearsDb) && $selectedYear && !in_array($selectedYear, $years)) {
            array_push($years, (int)$selectedYear);
            rsort($years);
        }

        $isPdf = true; // Flag untuk view PDF
        $data = compact(
            'expenses',
            'months',
            'years',
            'selectedMonth',
            'selectedYear',
            'searchName',
            'searchNote',
            'isPdf',
            'orderStatuses',         // Kirim ke view PDF
            'selectedOrderStatus'    // Kirim ke view PDF
        );

        $pdf = Pdf::loadView('pdf.expense_report_pdf', $data); // Menggunakan view PDF baru
        return $pdf->download('laporan-pengeluaran.pdf');
    }

    public function customerPayments(Request $request, string $status)
    {
        $query = DataPembayaran::whereHas('order', function ($query) use ($status) {
            $query->where('status', $status);
        })->with(['order.prospect', 'paymentMethod']) // Eager load relasi yang dibutuhkan
          ->orderBy('tgl_bayar', 'desc');

        // Ambil input filter
        $filterDateFrom = $request->input('date_from');
        $filterDateTo = $request->input('date_to');
        $filterPaymentMethodId = $request->input('payment_method_id');
        $filters = $request->only(['date_from', 'date_to', 'project_name', 'payment_method_id']);


        // Terapkan filter tanggal
        if ($filterDateFrom) {
            $query->whereDate('tgl_bayar', '>=', $filterDateFrom);
        }
        if ($filterDateTo) {
            $query->whereDate('tgl_bayar', '<=', $filterDateTo);
        }

        // Terapkan filter metode pembayaran
        if ($filterPaymentMethodId) {
            $query->where('payment_method_id', $filterPaymentMethodId);
        }

        // 4. Terapkan filter nama project (order name)
        if (!empty($filters['project_name'])) {
            $projectName = $filters['project_name'];
            $query->whereHas('order', function ($orderQuery) use ($projectName) {
                // Asumsi 'name' adalah kolom di tabel 'orders' yang ingin Anda filter
                $orderQuery->where('name', 'like', '%' . $projectName . '%');
                // Jika Anda juga ingin mencari di 'name_event' pada relasi 'prospect' dari 'order':
                $orderQuery->where('name', 'like', '%' . $projectName . '%')
                           ->orWhereHas('prospect', function ($prospectQuery) use ($projectName) {
                               $prospectQuery->where('name_event', 'like', '%' . $projectName . '%');
                           });
            });
        }

        $payments = $query->get();

        $totalPaymentsValue = $payments->sum('nominal');
        $pageTitle = "Customer Payments: " . Str::ucfirst(str_replace('_', ' ', $status)); // Membuat judul lebih rapi
        $paymentMethods = PaymentMethod::orderBy('name')->get(); // Ambil metode pembayaran untuk filter

        return view('reports.customer_payments_overview', [
            'payments' => $payments,
            'status' => $status,
            'totalPaymentsValue' => $totalPaymentsValue,
            'pageTitle' => $pageTitle,
            'paymentMethods' => $paymentMethods, // Kirim ke view
            'filters' => $request->only(['date_from', 'date_to', 'payment_method_id']), // Kirim nilai filter saat ini
        ]);
    }
}
