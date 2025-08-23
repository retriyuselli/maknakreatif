<?php

namespace App\Http\Controllers;

use App\Exports\ProductExport;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class ProductDisplayController extends Controller
{
    public function show(Product $product)
    {
        // Eager load relasi yang dibutuhkan
        $product->load(['category', 'items.vendor']);

        // Siapkan URL gambar
        $product->image_url = $product->image ? Storage::url($product->image) : asset('images/placeholder-product.png'); // Sesuaikan path placeholder

        // Kembalikan view dengan data produk
        return view('products.detail', compact('product'));
    }

    public function details(Product $product, string $action)
    {
        // Eager load necessary relationships if needed
        $product->load('category', 'items.vendor');

        if ($action === 'preview' || $action === 'print') {
            // Return a view for previewing/printing
            // You might have slightly different views or logic for print vs preview
            return view('products.details-preview', compact('product', 'action'));
        } elseif ($action === 'download') {
            // Load the PDF view
            $pdf = Pdf::loadView('products.details-preview', compact('product', 'action')); // <-- Use the new PDF view here
            // Generate and download a PDF
            // Example using barryvdh/laravel-dompdf:
            $pdf = Pdf::loadView('products.details-preview', compact('product'));
            return $pdf->download($product->slug . '-details.pdf');

            // Placeholder if PDF library is not set up yet
            return response("PDF download for '{$product->name}' not implemented yet.", 501);
        }

        // Handle invalid action
        abort(404, 'Invalid action specified.');
    }

    public function downloadPdf(Product $product)
    {
        // Load relasi yang mungkin dibutuhkan di view PDF (opsional tapi bagus untuk performa)
        $product->load(['category', 'items.vendor']);

        // Data yang akan dikirim ke view PDF
        $data = [
            'product' => $product,
            // Anda bisa menambahkan data lain di sini jika perlu
        ];

        // Load view 'products.pdf' dengan data
        $pdf = Pdf::loadView('products.pdf', $data);

        // (Opsional) Konfigurasi PDF
        // $pdf->setPaper('A4', 'portrait'); // Contoh: set ukuran kertas dan orientasi

        // Buat nama file yang dinamis
        $fileName = 'product-' . $product->slug . '-' . now()->format('Ymd') . '.pdf';

        // Kembalikan sebagai unduhan
        return $pdf->download($fileName);

        // Atau jika ingin menampilkan di browser dulu (inline)
        // return $pdf->stream($fileName);
    }

    public function exportDetailToExcel(Product $product)
    {
        return Excel::download(
            new ProductExport([$product->id]), // Menggunakan ProductExport yang sudah ada
            'product_detail_' . Str::slug($product->name) . '_' . now()->format('YmdHis') . '.xlsx'
        );
    }
}
