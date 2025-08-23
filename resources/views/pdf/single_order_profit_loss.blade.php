<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Laba Rugi - Order #{{ $order->number }}</title>
    <style>
        @page {
            /* Menambahkan margin 1.5cm di semua sisi (atas, kanan, bawah, kiri) */
            margin: 1.5cm;
        }
        /* Definisi Font Poppins untuk PDF Generator */
        @font-face {
            font-family: 'Poppins';
            src: url('{{ storage_path('app/fonts/poppins/Poppins-Regular.ttf') }}') format('truetype');
            font-weight: normal; /* 400 */
            font-style: normal;
        }
        @font-face {
            font-family: 'Poppins';
            src: url('{{ storage_path('app/fonts/poppins/Poppins-Bold.ttf') }}') format('truetype');
            font-weight: bold; /* 700 */
            font-style: normal;
        }
        @font-face {
            font-family: 'Poppins';
            src: url('{{ storage_path('app/fonts/poppins/Poppins-Italic.ttf') }}') format('truetype');
            font-weight: normal; /* 400 */
            font-style: italic;
        }
        @font-face {
            font-family: 'Poppins';
            src: url('{{ storage_path('app/fonts/poppins/Poppins-BoldItalic.ttf') }}') format('truetype');
            font-weight: bold; /* 700 */
            font-style: italic;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif; /* Pastikan semua elemen utama menggunakan Poppins */
        }
        body {
            /* font-family sudah di-set di '*' selector */
            font-size: 9pt;
            line-height: 1.4;
            color: #212529;
            margin: 50; /* Margin halaman diatur oleh @page */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }
        th, td {
            border: 1px solid #e2e8f0; /* Lighter border */
            padding: 0.6rem; /* Slightly more padding */
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #2d3748; /* Darker header */
            color: #ffffff; /* White text on header */
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
            letter-spacing: 0.5px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .whitespace-nowrap {
            white-space: nowrap;
        }
        .profit {
            color: #15803d; /* green-700 */
            font-weight: bold;
        }
        .loss {
            color: #b91c1c; /* red-700 */
            font-weight: bold;
        }
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
            border-bottom: 1px solid #adb5bd;
            padding-bottom: 0.25rem;
        }
        /* Styling untuk tabel ringkasan */
        .summary-table th {
            width: 65%; background-color: transparent; color: #212529; text-transform: none; font-size: 9pt; letter-spacing: normal;
        }
        .summary-table .total-row th,
        .summary-table .total-row td {
            font-weight: bold;
            background-color: #edf2f7; /* Light gray background for totals */
            border-top: 2px solid #a0aec0;
        }
        /* Styling untuk tabel detail */
        .details-table th, .details-table td {
            font-size: 8pt;
        }
        .details-table tbody tr:nth-child(even) {
            background-color: #f7fafc; /* Lighter zebra striping */
        }
        .text-muted {
            color: #6c757d;
            font-size: 7pt;
            font-weight: normal;
        }
        .no-data {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 1rem;
        }
        .header {
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .header h1 {
            font-size: 16pt;
            margin-bottom: 0.25rem;
        }
        .header .meta {
            font-size: 8pt;
            color: #6c757d;
        }
        .logo {
            max-height: 50px;
            margin-bottom: 0.5rem;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100pt;
            font-weight: bold;
            z-index: -1000;
            opacity: 0.8;
            text-align: center;
            width: 100%;
            letter-spacing: 10px;
            text-transform: uppercase;
        }
    </style>
</head>

<body>

    <div class="header">
        @php
            $logoPath = public_path('images/logomki.png');
            $logoSrc = '';
            if (file_exists($logoPath)) {
                try {
                    $logoSrc = 'data:' . mime_content_type($logoPath) . ';base64,' . base64_encode(file_get_contents($logoPath));
                } catch (\Exception $e) {
                    // Biarkan $logoSrc kosong jika ada error
                }
            }
        @endphp
        @if ($logoSrc)
            <img src="{{ $logoSrc }}" alt="Logo" class="logo">
        @endif
        <h1>Laporan Laba Rugi</h1>
    </div>

    @php
        // Kalkulasi diambil dari accessor model atau di-pass dari controller
        $grandTotal = $order->grand_total ?? 0;
        $totalPembayaranDiterima = $order->bayar ?? 0; // Menggunakan accessor 'bayar'
        $totalPengeluaran = $order->tot_pengeluaran ?? 0; // Menggunakan accessor 'tot_pengeluaran'
        $sisaPembayaran = $grandTotal - $totalPembayaranDiterima;
        $labaKotor = $order->laba_kotor ?? 0; // Menggunakan accessor 'laba_kotor'
        $isPaid = $order->is_paid;
        $watermarkText = $isPaid ? 'LUNAS' : 'BELUM LUNAS';
        $watermarkColor = $isPaid ? 'rgba(22, 163, 74, 0.1)' : 'rgba(220, 38, 38, 0.08)';
    @endphp

    <div class="watermark" style="color: {{ $watermarkColor }};">
        {{ $watermarkText }}
    </div>

    <table style="width: 100%; border: none; margin-bottom: 1.5rem;">
        <tr>
            <td style="width: 50%; border: none; padding: 0;">
                <strong>Order #:</strong> {{ $order->number }}<br>
                <strong>Event:</strong> {{ $order->prospect?->name_event ?? 'N/A' }}
            </td>
            <td style="width: 50%; border: none; padding: 0; text-align: right;">
                <strong>Dicetak pada:</strong> {{ $generatedDate }}<br>
                <strong>Status:</strong> <span class="{{ $isPaid ? 'profit' : 'loss' }}">{{ $isPaid ? 'Lunas' : 'Belum Lunas' }}</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Ringkasan Keuangan</div>
    <table class="summary-table">
        <tr>
            <th>Total Paket Awal</th>
            <td class="text-right whitespace-nowrap">Rp {{ number_format($order->total_price ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Penambahan</th>
            <td class="text-right whitespace-nowrap">Rp {{ number_format($order->penambahan ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Promo</th>
            <td class="text-right whitespace-nowrap">Rp {{ number_format($order->promo ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Pengurangan</th>
            <td class="text-right whitespace-nowrap">Rp {{ number_format($order->pengurangan ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr class="total-row">
            <th>Grand Total (Paket)</th>
            <td class="text-right whitespace-nowrap">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Total Pembayaran Diterima</th>
            <td class="text-right whitespace-nowrap">Rp {{ number_format($totalPembayaranDiterima, 0, ',', '.') }}</td>
        </tr>
         <tr>
            <th>Sisa Pembayaran Klien</th>
            <td class="text-right whitespace-nowrap">Rp {{ number_format($sisaPembayaran, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Total Pengeluaran</th>
            <td class="text-right whitespace-nowrap">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</td>
        </tr>
        <tr class="total-row">
            <th>
                Laba / Rugi Kotor
                <br>
                <span class="text-muted">Grand Total (Paket) - Total Pengeluaran</span>
            </th>
            <td class="text-right whitespace-nowrap {{ $labaKotor >= 0 ? 'profit' : 'loss' }}">
                Rp {{ number_format($labaKotor, 0, ',', '.') }}
            </td>
        </tr>
    </table>

    @if($order->dataPembayaran->count() > 0)
        <div class="section-title">Detail Pembayaran Diterima</div>
        <table class="details-table">
            <thead>
                <tr>
                    <th class="whitespace-nowrap">Tanggal Bayar</th>
                    <th>Metode</th>
                    <th>Keterangan</th>
                    <th class="text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->dataPembayaran as $pembayaran)
                <tr>
                    <td class="whitespace-nowrap">{{ \Carbon\Carbon::parse($pembayaran->tanggal_bayar)->format('d M Y') }}</td>
                    <td>{{ $pembayaran->paymentMethod?->name ?? 'N/A' }}</td>
                    <td>{{ $pembayaran->keterangan ?? '-' }}</td>
                    <td class="text-right whitespace-nowrap">Rp {{ number_format($pembayaran->nominal ?? 0, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">Belum ada data pembayaran diterima.</div>
    @endif

    @if($order->expenses->count() > 0)
        <div class="section-title">Detail Pengeluaran</div>
        <table class="details-table">
            <thead>
                <tr>
                    <th class="whitespace-nowrap">Tanggal</th>
                    <th>Vendor</th>
                    <th class="whitespace-nowrap">No. ND</th>
                    <th>Keterangan</th>
                    <th class="text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->expenses as $expense)
                <tr>
                    <td class="whitespace-nowrap">{{ $expense->date_expense ? \Carbon\Carbon::parse($expense->date_expense)->format('d M Y') : '-' }}</td>
                    <td>{{ $expense->vendor?->name ?? 'N/A' }}</td>
                    <td class="whitespace-nowrap">{{ $expense->no_nd ? 'ND-0'.$expense->no_nd : '-' }}</td>
                    <td>{{ $expense->note ?? '-' }}</td>
                    <td class="text-right whitespace-nowrap">Rp {{ number_format($expense->amount ?? 0, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">Belum ada data pengeluaran untuk order ini.</div>
    @endif

    {{-- Skrip ini akan dieksekusi oleh dompdf untuk menambahkan footer di setiap halaman --}}
    <script type="text/php">
        if (isset($pdf)) {
            // Definisikan font dan ukuran untuk footer
            $font = $fontMetrics->getFont("Poppins", "normal");
            $size = 7; // Ukuran font kecil agar tidak mengganggu

            // Posisi Y untuk footer, sedikit di atas margin bawah halaman
            $y = $pdf->get_height() - 30;

            // Teks di sisi kiri: Nama Perusahaan
            $leftText = "PT. Makna Kreatif Indonesia | Laporan Laba Rugi Order: {{ $order->number }}";
            $pdf->page_text(30, $y, $leftText, $font, $size);

            // Teks di sisi kanan: Nomor Halaman
            $rightText = "Halaman {PAGE_NUM} dari {PAGE_COUNT}";
            // Hitung lebar teks kanan agar posisinya pas di kanan
            $width = $fontMetrics->get_text_width($rightText, $font, $size);
            $pdf->page_text($pdf->get_width() - $width - 30, $y, $rightText, $font, $size);
        }
    </script>
</body>

</html>