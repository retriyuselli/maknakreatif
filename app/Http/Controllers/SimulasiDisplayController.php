<?php

namespace App\Http\Controllers;

use App\Models\SimulasiProduk;
use Illuminate\View\View; // Import View
use Barryvdh\DomPDF\Facade\Pdf; // atau use PDF; jika Anda menambahkan alias

class SimulasiDisplayController extends Controller
{
    /**
     * Display the specified simulasi produk.
     *
     * @param  SimulasiProduk  $record
     * @return View
     */
    public function show(SimulasiProduk $record): View
    {
        $items = collect();
        if ($record->product) {
            // Eager load vendors for items to prevent N+1 queries in the view
            $items = $record->product->items()->with('vendor')->get();
        }

        // The view 'simulasi.show' (likely resources/views/simulasi/invoice.blade.php)
        // expects the SimulasiProduk object as 'simulasi' and items as 'items'.
        // Financial details like subtotal, promo, grand_total can be accessed
        // directly from the 'simulasi' object in the view (e.g., $simulasi->grand_total).
        // The 'SimulasiProduk' model already has accessors for these if needed.
        
        // Pass 'record' as 'simulasi' to match the view variable name
        return view('simulasi.show', [
            'simulasi' => $record, 
            'items' => $items,
            'pengurangans' => $record->pengurangans
        ]);
    }

    public function downloadPdf(SimulasiProduk $record) // Menggunakan Route Model Binding
    {
        // Ambil item-item dari produk dasar jika ada
        $items = collect();
        if ($record->product) {
            // Asumsi model Product memiliki relasi 'items' ke ProductVendor
            // dan setiap ProductVendor memiliki relasi 'vendor'
            // Eager load vendor untuk menghindari N+1 query di view
            $items = $record->product->items()->with('vendor')->get();
        }

        // Data yang akan dilewatkan ke view
        // Variabel total (subtotal, promo, dll.) sudah ada di $record
        $data = [
            'record' => $record,
            'items' => $items,
            // Anda bisa melewatkan variabel total secara eksplisit jika diperlukan,
            // tapi karena $record sudah memilikinya, ini mungkin tidak perlu.
            // 'subtotal' => $record->total_price,
            // 'promo' => $record->promo,
            // 'penambahan' => $record->penambahan,
            // 'pengurangan' => $record->pengurangan,
            // 'grand_total' => $record->grand_total,
        ];

        // Render view 'simulasi.show' dengan data
        // Pastikan path view sudah benar
        $pdf = Pdf::loadView('pdf.simulasi', $data);

        // Atur ukuran kertas dan orientasi jika perlu (opsional)
        // $pdf->setPaper('a4', 'portrait');

        // Buat nama file PDF yang dinamis
        $fileName = 'simulasi_penawaran_' . $record->slug . '_' . now()->format('Ymd') . '.pdf';

        // Download PDF
        return $pdf->download($fileName);

        // Atau jika ingin menampilkan di browser terlebih dahulu (inline)
        // return $pdf->stream($fileName);
    }
}
