<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\DataPembayaran;
use App\Models\Expense;
use App\Models\ExpenseOps;
use App\Models\PendapatanLain;
use App\Models\PengeluaranLain;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class LaporanKeuangan extends Page
{
    use HasPageShield;
    
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.laporan-keuangan';

    public $transaksi = [];
    public $tanggal_awal;
    public $tanggal_akhir;
    public $total_masuk = 0;
    public $total_keluar = 0;
    public $filter_jenis = '';
    public $filter_keyword = '';

    public function mount()
    {
        // Set tanggal awal dan akhir ke bulan berjalan
        $this->tanggal_awal = now()->startOfMonth()->toDateString();
        $this->tanggal_akhir = now()->endOfMonth()->toDateString();
        $this->transaksi = $this->getTransaksiGabungan();
        $this->total_masuk = $this->hitungTotalMasuk();
        $this->total_keluar = $this->hitungTotalKeluar();
    }

    public function getTransaksiGabungan(): Collection
    {
        $start = $this->tanggal_awal;
        $end = $this->tanggal_akhir;

        $uangMasuk = DataPembayaran::whereBetween('tgl_bayar', [$start, $end])
            ->select(
                DB::raw('tgl_bayar as tanggal'),
                DB::raw('nominal as jumlah'),
                DB::raw('"Masuk (Wedding)" as jenis'),
                DB::raw('keterangan as deskripsi'),
                DB::raw('order_id'),
                DB::raw('(
                    SELECT name_event 
                    FROM prospects 
                    WHERE prospects.id = (
                        SELECT prospect_id 
                        FROM orders 
                        WHERE orders.id = data_pembayarans.order_id
                        LIMIT 1
                    ) 
                    LIMIT 1
                ) as prospect_name'),
                DB::raw('NULL as vendor_name'), // Kolom vendor_name untuk konsistensi union
                DB::raw('(
                    SELECT CONCAT(name, " (", no_rekening, ")")
                    FROM payment_methods
                    WHERE payment_methods.id = data_pembayarans.payment_method_id
                    LIMIT 1
                ) as payment_method_details')
            );

        $pengeluaranWedding = Expense::whereBetween('date_expense', [$start, $end])
            ->select(
                DB::raw('date_expense as tanggal'),
                DB::raw('amount as jumlah'),
                DB::raw('"Keluar (Wedding)" as jenis'),
                DB::raw('note as deskripsi'),
                DB::raw('order_id'),
                DB::raw('(
                    SELECT name_event 
                    FROM prospects 
                    WHERE prospects.id = (
                        SELECT prospect_id 
                        FROM orders 
                        WHERE orders.id = expenses.order_id
                        LIMIT 1
                    ) 
                    LIMIT 1
                ) as prospect_name'), // Mengambil prospect_name dari order terkait
                DB::raw('(
                    SELECT name 
                    FROM vendors 
                    WHERE vendors.id = expenses.vendor_id
                    LIMIT 1
                ) as vendor_name'), // Menambahkan nama vendor
                DB::raw('(
                    SELECT CONCAT(name, " (", no_rekening, ")")
                    FROM payment_methods
                    WHERE payment_methods.id = expenses.payment_method_id
                    LIMIT 1
                ) as payment_method_details')
            );

        $pengeluaranOps = ExpenseOps::whereBetween('date_expense', [$start, $end])
            ->select(
                DB::raw('date_expense as tanggal'),
                DB::raw('amount as jumlah'),
                DB::raw('"Keluar (Operasional)" as jenis'),
                DB::raw('note as deskripsi'),
                DB::raw('NULL as order_id'),
                DB::raw('NULL as prospect_name'), // ExpenseOps tidak memiliki prospect_name
                DB::raw('name as vendor_name'), // Menggunakan name dari ExpenseOps sebagai vendor_name
                DB::raw('(
                    SELECT CONCAT(name, " (", no_rekening, ")")
                    FROM payment_methods
                    WHERE payment_methods.id = expense_ops.payment_method_id
                    LIMIT 1
                ) as payment_method_details')
            );

        $pendapatanLain = PendapatanLain::whereBetween('tgl_bayar', [$start, $end])
            ->select(
                DB::raw('tgl_bayar as tanggal'),
                DB::raw('nominal as jumlah'),
                DB::raw('"Masuk (Lain-lain)" as jenis'),
                DB::raw('keterangan as deskripsi'),
                DB::raw('NULL as order_id'),
                DB::raw('NULL as prospect_name'),
                DB::raw('NULL as vendor_name'),
                DB::raw('(
                    SELECT CONCAT(name, " (", no_rekening, ")")
                    FROM payment_methods
                    WHERE payment_methods.id = pendapatan_lains.payment_method_id
                    LIMIT 1
                ) as payment_method_details')
            );

        $pengeluaranLain = PengeluaranLain::whereBetween('date_expense', [$start, $end])
            ->select(
                DB::raw('date_expense as tanggal'),
                DB::raw('amount as jumlah'),
                DB::raw('"Keluar (Lain-lain)" as jenis'),
                DB::raw('note as deskripsi'),
                DB::raw('NULL as order_id'),
                DB::raw('NULL as prospect_name'),
                DB::raw('NULL as vendor_name'),
                DB::raw('(
                    SELECT CONCAT(name, " (", no_rekening, ")")
                    FROM payment_methods
                    WHERE payment_methods.id = pengeluaran_lains.payment_method_id
                    LIMIT 1
                ) as payment_method_details')
            );

        $all = $uangMasuk
            ->unionAll($pendapatanLain)
            ->unionAll($pengeluaranWedding)
            ->unionAll($pengeluaranOps)
            ->unionAll($pengeluaranLain);

        $data = $all->orderBy('tanggal', 'desc')->get();

        // Hitung saldo berjalan dengan urutan yang benar (dari tanggal terlama ke terbaru)
        $dataUrut = $data->sortBy('tanggal');
        $saldo = 0;
        
        $dataUrut = $dataUrut->map(function ($item) use (&$saldo) {
            if (str_contains($item->jenis, 'Masuk')) {
                $saldo += $item->jumlah;
            } else {
                $saldo -= $item->jumlah;
            }
            $item->saldo = $saldo;
            return $item;
        });

        // Kembalikan ke urutan desc untuk tampilan
        return $dataUrut->sortByDesc('tanggal')->values();
    }

    public function updated($propertyName)
    {
        // Tidak perlu filter otomatis pada update, gunakan filter() saja
    }

    public function filter()
    {
        $transaksi = $this->getTransaksiGabungan();

        // Filter jenis transaksi
        if ($this->filter_jenis && $this->filter_jenis !== 'semua') {
            $transaksi = $transaksi->filter(function ($item) {
                return $item->jenis === $this->filter_jenis;
            });
        }

        // Filter keyword di deskripsi dan nama prospect/event
        if ($this->filter_keyword) {
            $transaksi = $transaksi->filter(function ($item) {
                $keyword = $this->filter_keyword;

                // Cek apakah keyword ada di kolom deskripsi
                $inDeskripsi = stripos($item->deskripsi, $keyword) !== false;

                // Cek apakah keyword ada di kolom prospect_name (jika tidak kosong)
                $inProspect = !empty($item->prospect_name) && stripos($item->prospect_name, $keyword) !== false;

                // Cek apakah keyword ada di kolom vendor_name (jika tidak kosong)
                $inVendor = !empty($item->vendor_name) && stripos($item->vendor_name, $keyword) !== false;

                // Cek apakah keyword ada di kolom payment_method_details
                $inPaymentMethod = !empty($item->payment_method_details) && stripos($item->payment_method_details, $keyword) !== false;

                return $inDeskripsi || $inProspect || $inVendor || $inPaymentMethod;
            });
        }

        // Hitung ulang saldo berjalan setelah filter diterapkan
        $this->transaksi = $this->hitungSaldoBerjalan($transaksi);
        $this->total_masuk = $this->hitungTotalMasuk();
        $this->total_keluar = $this->hitungTotalKeluar();
    }

    public function resetFilters()
    {
        $this->filter_jenis = '';
        $this->filter_keyword = '';
        $this->tanggal_awal = now()->startOfMonth()->toDateString();
        $this->tanggal_akhir = now()->endOfMonth()->toDateString();

        $this->filter();
    }

    public function hitungTotalMasuk()
    {
        return collect($this->transaksi)
            ->filter(function ($item) {
                return str_contains($item->jenis, 'Masuk');
            })
            ->sum('jumlah');
    }

    public function hitungTotalKeluar()
    {
        return collect($this->transaksi)
            ->filter(function ($item) {
                return str_contains($item->jenis, 'Keluar');
            })
            ->sum('jumlah');
    }

    public function hitungSaldoBerjalan($transaksi)
    {
        // Urutkan berdasarkan tanggal dari terlama ke terbaru
        $dataUrut = $transaksi->sortBy('tanggal');
        $saldo = 0;
        
        $dataUrut = $dataUrut->map(function ($item) use (&$saldo) {
            if (str_contains($item->jenis, 'Masuk')) {
                $saldo += $item->jumlah;
            } else {
                $saldo -= $item->jumlah;
            }
            $item->saldo = $saldo;
            return $item;
        });

        // Kembalikan ke urutan desc untuk tampilan
        return $dataUrut->sortByDesc('tanggal')->values();
    }

    public function downloadPdf()
    {
        // Cek jumlah data sebelum download
        $currentData = collect($this->transaksi);
        $dataCount = $currentData->count();
        
        // Beri peringatan jika data terlalu banyak
        if ($dataCount > 1000) {
            session()->flash('warning', 'Data yang akan di-download sangat banyak (' . $dataCount . ' record). PDF akan dibatasi maksimal 1000 record teratas. Gunakan filter yang lebih spesifik untuk hasil yang lebih baik.');
        } elseif ($dataCount > 500) {
            session()->flash('info', 'Data yang akan di-download cukup banyak (' . $dataCount . ' record). Proses generate PDF mungkin membutuhkan waktu lebih lama.');
        }
        
        // Buat URL dengan parameter untuk download
        $params = [
            'tanggal_awal' => $this->tanggal_awal,
            'tanggal_akhir' => $this->tanggal_akhir,
            'filter_jenis' => $this->filter_jenis,
            'filter_keyword' => $this->filter_keyword
        ];
        
        $url = route('laporan-keuangan.download-pdf', $params);
        
        // Menggunakan JavaScript untuk membuka link download
        $this->dispatch('download-pdf', url: $url);
    }

    // Static method untuk handle download dari route
    public static function handleDownloadPdf(Request $request)
    {
        // Buat instance baru dari class ini
        $instance = new static();
        
        // Set parameter dari request
        $instance->tanggal_awal = $request->get('tanggal_awal', now()->startOfMonth()->toDateString());
        $instance->tanggal_akhir = $request->get('tanggal_akhir', now()->endOfMonth()->toDateString());
        $instance->filter_jenis = $request->get('filter_jenis', '');
        $instance->filter_keyword = $request->get('filter_keyword', '');

        // Dapatkan data berdasarkan filter yang sedang aktif
        $transaksi = $instance->getTransaksiGabungan();

        // Filter jenis transaksi
        if ($instance->filter_jenis && $instance->filter_jenis !== 'semua') {
            $transaksi = $transaksi->filter(function ($item) use ($instance) {
                return $item->jenis === $instance->filter_jenis;
            });
        }

        // Filter keyword di deskripsi dan nama prospect/event
        if ($instance->filter_keyword) {
            $transaksi = $transaksi->filter(function ($item) use ($instance) {
                $keyword = $instance->filter_keyword;

                // Cek apakah keyword ada di kolom deskripsi
                $inDeskripsi = stripos($item->deskripsi, $keyword) !== false;

                // Cek apakah keyword ada di kolom prospect_name (jika tidak kosong)
                $inProspect = !empty($item->prospect_name) && stripos($item->prospect_name, $keyword) !== false;

                // Cek apakah keyword ada di kolom payment_method_details
                $inPaymentMethod = !empty($item->payment_method_details) && stripos($item->payment_method_details, $keyword) !== false;

                return $inDeskripsi || $inProspect || $inPaymentMethod;
            });
        }

        // Hitung ulang saldo berjalan setelah filter diterapkan dengan method yang sama
        $transaksi = $instance->hitungSaldoBerjalan($transaksi);

        // Jika data terlalu banyak (lebih dari 500 record), batasi untuk performa
        $totalRecords = $transaksi->count();
        $maxRecords = 1000; // Batas maksimal record untuk PDF
        
        if ($totalRecords > $maxRecords) {
            // Ambil data terbaru saja
            $transaksi = $transaksi->take($maxRecords);
            $isLimited = true;
        } else {
            $isLimited = false;
        }

        // Hitung total masuk dan keluar dari data yang sudah difilter
        $totalMasuk = $transaksi->filter(function ($item) {
            return str_contains($item->jenis, 'Masuk');
        })->sum('jumlah');

        $totalKeluar = $transaksi->filter(function ($item) {
            return str_contains($item->jenis, 'Keluar');
        })->sum('jumlah');

        $saldoAkhir = $totalMasuk - $totalKeluar;

        // Data untuk PDF
        $data = [
            'transaksi' => $transaksi,
            'tanggal_awal' => $instance->tanggal_awal,
            'tanggal_akhir' => $instance->tanggal_akhir,
            'filter_jenis' => $instance->filter_jenis,
            'filter_keyword' => $instance->filter_keyword,
            'total_masuk' => $totalMasuk,
            'total_keluar' => $totalKeluar,
            'saldo_akhir' => $saldoAkhir,
            'generated_at' => now()->format('d/m/Y H:i:s'),
            'total_records' => $totalRecords,
            'is_limited' => $isLimited,
            'max_records' => $maxRecords
        ];

        // Generate PDF dengan error handling
        try {
            $pdf = Pdf::loadView('pdf.laporan-keuangan', $data);
            $pdf->setPaper('A4', 'landscape');
            
            // Set options untuk handle data banyak
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'defaultFont' => 'Poppins',
                'isRemoteEnabled' => true,
                'chroot' => public_path(),
                'dpi' => 72, // Turunkan DPI untuk performa lebih baik
            ]);
            
            // Set memory limit dan max execution time untuk data banyak
            ini_set('memory_limit', '512M');
            ini_set('max_execution_time', 300); // 5 menit

            // Buat nama file yang informatif
            $fileName = 'Laporan_Keuangan_' . 
                       str_replace('-', '', $instance->tanggal_awal) . '_' . 
                       str_replace('-', '', $instance->tanggal_akhir) . '_' . 
                       now()->format('YmdHis') . '.pdf';

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $fileName);
            
        } catch (\Exception $e) {
            // Jika error karena data terlalu banyak, coba dengan data yang lebih sedikit
            if ($totalRecords > 500) {
                $transaksi = $transaksi->take(500);
                $data['transaksi'] = $transaksi;
                $data['is_limited'] = true;
                $data['max_records'] = 500;
                $data['error_message'] = 'Data dibatasi 500 record teratas karena terlalu banyak';
                
                try {
                    $pdf = Pdf::loadView('pdf.laporan-keuangan', $data);
                    $pdf->setPaper('A4', 'landscape');
                    $pdf->setOptions([
                        'isHtml5ParserEnabled' => true,
                        'isPhpEnabled' => true,
                        'defaultFont' => 'Poppins',
                        'dpi' => 72,
                    ]);
                    
                    $fileName = 'Laporan_Keuangan_Limited_' . 
                               str_replace('-', '', $instance->tanggal_awal) . '_' . 
                               str_replace('-', '', $instance->tanggal_akhir) . '_' . 
                               now()->format('YmdHis') . '.pdf';

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, $fileName);
                    
                } catch (\Exception $e2) {
                    // Jika masih error, return error response
                    return response()->json([
                        'error' => 'Gagal generate PDF: ' . $e2->getMessage(),
                        'message' => 'Data terlalu banyak untuk di-generate. Silakan gunakan filter yang lebih spesifik.'
                    ], 500);
                }
            }
            
            // Return error response untuk error lainnya
            return response()->json([
                'error' => 'Gagal generate PDF: ' . $e->getMessage(),
                'message' => 'Terjadi kesalahan saat membuat PDF. Silakan coba lagi.'
            ], 500);
        }
    }
}
