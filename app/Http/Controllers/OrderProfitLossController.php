<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf; // Pastikan facade PDF di-import
use Illuminate\Support\Facades\Log;

class OrderProfitLossController extends Controller
{
    /**
     * Display the profit and loss preview for a specific order.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function preview(Order $order)
    {
        // Load relasi yang dibutuhkan (jika belum eager loaded)
        $order->loadMissing(['prospect', 'dataPembayaran.paymentMethod', 'expenses.vendor']);

        // Ambil total pembayaran diterima menggunakan accessor dari model Order
        $totalPembayaranDiterima = $order->bayar;

        // Data yang akan dikirim ke view
        $reportData = [
            'order' => $order,
            'totalPembayaranDiterima' => $totalPembayaranDiterima,
            'generatedDate' => now()->format('d M Y H:i'),
        ];

        // Tampilkan view Blade untuk preview
        // Anda bisa menggunakan view yang sama dengan yang digunakan untuk generate PDF
        // atau membuat view khusus untuk preview HTML jika diinginkan.
        // Di sini kita asumsikan menggunakan view yang sama dengan PDF.
        return view('orders.profit_loss_preview', $reportData);

        /*
        Alternatif: Jika Anda ingin preview ini menghasilkan PDF juga
        $pdf = Pdf::loadView('pdf.single_order_profit_loss', $reportData);
        return $pdf->stream('laba_rugi_order_' . $order->number . '_' . now()->format('YmdHis') . '.pdf');
        */
    }

    /**
     * Generate and download the profit and loss PDF for a specific order.
     * (Fungsi ini mungkin sudah ada di tempat lain atau bisa ditambahkan di sini)
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function downloadPdf(Order $order)
    {
        $order->loadMissing(['prospect', 'dataPembayaran.paymentMethod', 'expenses.vendor']);

        // Ambil total pembayaran diterima menggunakan accessor dari model Order
        $totalPembayaranDiterima = $order->bayar;

        $reportData = [
            'order' => $order,
            'totalPembayaranDiterima' => $totalPembayaranDiterima,
            'generatedDate' => now()->format('d M Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.single_order_profit_loss', $reportData);

        return $pdf->download('laba_rugi_order_' . $order->number . '_' . now()->format('YmdHis') . '.pdf');
    }

    public function download(Order $order)
    {
        try {
            // Pastikan relasi yang dibutuhkan sudah di-load
            $order->loadMissing(['dataPembayaran', 'expenses']);

            // Siapkan data untuk view PDF
            $reportData = [
                'order' => $order,
                'generatedDate' => now()->format('d M Y H:i'),
            ];

            // Asumsikan Anda memiliki view 'pdf.single_order_profit_loss'
            // Jika belum ada, Anda bisa menyalin dari 'orders.profit_loss_preview' dan menyesuaikannya untuk PDF
            $pdf = Pdf::loadView('pdf.single_order_profit_loss', $reportData);

            // Kembalikan response download
            // return $pdf->stream('laba_rugi_order_' . $order->number . '_' . now()->format('YmdHis') . '.pdf');
            return $pdf->download('laba_rugi_order_' . $order->number . '_' . now()->format('YmdHis') . '.pdf');

        } catch (\Exception $e) {
            Log::error("Error generating PDF for order {$order->id}: " . $e->getMessage());
            // Redirect kembali dengan pesan error
            return back()->with('error', 'Gagal membuat laporan PDF: ' . $e->getMessage());
        }
    }
}
