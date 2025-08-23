<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Laba Rugi</title> {{-- Judul ini untuk metadata browser saat HTML dirender, tidak langsung ke PDF --}}
    <style>
        @font-face {
            font-family: 'Poppins';
            font-style: normal;
            font-weight: 400;
            src: url('{{ storage_path('fonts/Poppins-Regular.ttf') }}') format('truetype');
        }
        @font-face {
            font-family: 'Poppins';
            font-style: normal;
            font-weight: 700; /* bold */
            src: url('{{ storage_path('fonts/Poppins-Bold.ttf') }}') format('truetype');
        }
        // Jika Anda membutuhkan berat lain, tambahkan @font-face serupa:
        @font-face {
            font-family: 'Poppins';
            font-style: normal;
            font-weight: 600; // SemiBold
            src: url('{{ storage_path('fonts/Poppins-SemiBold.ttf') }}') format('truetype');
        }
        .container {
            width: 100%; /* Full width for PDF */
            margin: 20px auto; /* Adjust margin as needed */
            padding: 0 20px; /* Padding for content */
            box-sizing: border-box;
            }

        body { font-family: 'Poppins', 'Helvetica', 'Arial', sans-serif; font-size: 10px; line-height: 1; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        th, td { border: 1px solid #ccc; padding: 5px; text-align: left; vertical-align: top; }
        th { background-color: #e9e9e9; font-weight: 700; /* Menggunakan berat bold dari Poppins */ }
        .total-row td { font-weight: bold; background-color: #f5f5f5; }
        .summary { margin-top: 20px; padding: 15px; border: 1px solid #eee; background-color: #fdfdfd; }
        .summary h3 { margin-top: 0; font-size: 12px; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 10px;}
        .summary p { margin: 5px 0; display: flex; justify-content: space-between; }
        .summary span { font-weight: normal; }
        .text-right { text-align: right; }
        .profit { color: #28a745; } /* Green */
        .loss { color: #dc3545; } /* Red */
        h1, h2 { text-align: center; margin-bottom: 5px; margin-top: 0; }
        h1 { font-size: 16px; }
        h2 { font-size: 14px; margin-bottom: 20px; }
        .meta { font-size: 9px; color: #555; margin-bottom: 15px; text-align: center; }
        hr { border: 0; border-top: 1px solid #eee; margin: 10px 0; }
        .number { white-space: nowrap; } /* Prevent wrapping for numbers */
        .company-logo {
            max-height: 2.5rem;
            margin-left: auto;
            margin-right: auto;
            margin-bottom: 1rem;
        }
        .company-address {
            font-size: 9px;
            color: #555;
            margin-bottom: 10px;
            text-align: center;
        }
    </style>
    <style>
        /* Styles for Signature Section */
        .signature-section {
            margin-top: 40px; /* Space above the signature section */
            width: 100%;
            display: table; /* Use table display for columns */
            table-layout: fixed; /* Fix column widths */
        }
        .signature-column { display: table-cell; width: 50%; text-align: center; padding: 0 10px; }
        .signature-line { margin-top: 50px; border-bottom: 1px solid #000; width: 70%; margin-left: auto; margin-right: auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="report-title-header">
            @php
                $logoPath = public_path('images/logomki.png');
                $logoSrc = '';
                if (file_exists($logoPath)) {
                    // Embedding as base64 is generally more reliable for DomPDF
                    $logoMime = mime_content_type($logoPath);
                    if ($logoMime) {
                        $logoSrc = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
                    }
                }
            @endphp
            @if($logoSrc)<img src="{{ $logoSrc }}" alt="Logo Perusahaan" class="company-logo">@endif
            <p class="company-address">Jl. Sintraman Jaya I No. 2148, 20 Ilir D II, Kec. Kemuning, Kota Palembang, Sumatera Selatan 30137 <br>
            PT. Makna Kreatif Indonesia | maknawedding@gmail.com | +62 822-9796-2600
            </p>
                <h1>Laporan Laba Rugi Klien</h1>
                    <div class="meta">
                        Dicetak pada: {{ $generatedDate }} <br>
                        @if($filterStartDate || $filterEndDate)
                            Periode Filter:
                            {{ $filterStartDate ? \Carbon\Carbon::parse($filterStartDate)->format('d M Y') : 'Awal' }} -
                            {{ $filterEndDate ? \Carbon\Carbon::parse($filterEndDate)->format('d M Y') : 'Akhir' }}
                        @else
                            Periode Filter: Semua Order
                        @endif
                </h1>
            </div>
        </div>
    </div>

    {{-- <h2>Detail Order</h2> --}}
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 30%;">Nama Event</th>
                <th style="width: 15%;">Tgl Closing</th>
                <th class="text-right" style="width: 12%;">Total Pemasukan</th>
                <th class="text-right" style="width: 13%;">Nilai Order (Grand Total)</th>
                <th class="text-right" style="width: 12%;">Total Pengeluaran</th>
                <th class="text-right" style="width: 13%;">Laba / Rugi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                @php
                    // Calculate sum of payments for the current order
                    // Ensure dataPembayaran relationship is loaded if dealing with many orders to avoid N+1
                    $totalPembayaranDiterimaOrder = $order->dataPembayaran()->sum('nominal');
                    $profitLoss = ($order->grand_total ?? 0) - ($order->tot_pengeluaran ?? 0);
                @endphp
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $order->prospect?->name_event ?? 'N/A' }}</td>
                    <td>{{ $order->closing_date ? \Carbon\Carbon::parse($order->closing_date)->format('d M Y') : '-' }}</td>
                    <td class="text-right number">Rp {{ number_format($totalPembayaranDiterimaOrder ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right number">Rp {{ number_format($order->grand_total ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right number">Rp {{ number_format($order->tot_pengeluaran ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right number {{ $profitLoss >= 0 ? 'profit' : 'loss' }}">
                        Rp {{ number_format($profitLoss, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">Tidak ada data order yang ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-left"><strong>Total Keseluruhan:</strong></td> 
                {{-- Colspan adjusted to 3 to cover "Order #", "Nama Event", "Tgl Closing" --}}
                <td class="text-right number"><strong>Rp {{ number_format($totalIncome, 0, ',', '.') }}</strong></td>
                {{-- $totalIncome now sums the "Total Pemasukan (Diterima)" column.
                     Controller needs to be updated if $totalIncome was previously sum of grand_total. --}}
                <td class="text-right number"><strong>Rp {{ number_format($totalExpenses, 0, ',', '.') }}</strong></td>
                {{-- $totalExpenses now sums the "Nilai Order (Grand Total)" column.
                     Controller needs to be updated if $totalExpenses was previously sum of tot_pengeluaran. --}}
                <td class="text-right number"><strong>Rp {{ number_format($sumAllOrdersPengeluaran ?? 0, 0, ',', '.') }}</strong></td>
                {{-- This cell is for the sum of "Total Pengeluaran" (column 6).
                     Ensure $sumAllOrdersPengeluaran is passed from the controller. --}}
                <td class="text-right number {{ $netProfit >= 0 ? 'profit' : 'loss' }}">
                    <strong>Rp {{ number_format($netProfit, 0, ',', '.') }}</strong>
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- Note: The tfoot above sums the 4th, 5th, and 7th columns.
         The sum of the 6th column (Total Pengeluaran) is not currently displayed in the tfoot. --}}
    {{-- Pastikan variabel $eventSummary dikirim dari Controller --}}
    @isset($eventSummary)
        @if(count($eventSummary) > 0)
            <h2>Ringkasan per Event</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nama Event</th>
                        <th class="text-right">Total Pemasukan</th>
                        <th class="text-right">Total Pengeluaran</th>
                        <th class="text-right">Laba / Rugi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($eventSummary as $summary)
                        <tr>
                            <td>{{ $summary['name_event'] ?? 'N/A' }}</td>
                            <td class="text-right number">Rp {{ number_format($summary['total_income'] ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right number">Rp {{ number_format($summary['total_expenses'] ?? 0, 0, ',', '.') }}</td>
                            @php $eventProfitLoss = ($summary['total_income'] ?? 0) - ($summary['total_expenses'] ?? 0); @endphp
                            <td class="text-right number {{ $eventProfitLoss >= 0 ? 'profit' : 'loss' }}">
                                Rp {{ number_format($eventProfitLoss, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endisset

    {{-- Signature Section --}}
    <div class="signature-section">
        <div class="signature-column">
            <p>Disiapkan Oleh,</p>
            <br>
            <div class="signature-line"></div>
            <p style="margin-top: 5px;">( Finance )</p>
        </div>
        <div class="signature-column">
            <p>Disetujui Oleh,</p>
            <br>
            <div class="signature-line"></div>
            <p style="margin-top: 5px;">( Pimpinan )</p>
        </div>
    </div>

</body>
</html>