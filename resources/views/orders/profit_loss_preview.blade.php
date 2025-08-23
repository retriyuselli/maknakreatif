<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Laba Rugi - Order #{{ $order->number }}</title>
    {{-- Menggunakan Poppins dari Google Fonts untuk tampilan web --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    {{-- Sertakan Tailwind CSS dari CDN untuk kemudahan --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Konfigurasi dasar Tailwind (opsional, untuk menyesuaikan font, dll.)
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    },
                }
            }
        }
    </script>
</head>

<body class="bg-gray-100 text-gray-800 font-sans p-6 md:p-10 leading-relaxed">
    <div class="max-w-4xl mx-auto bg-white p-8 md:p-12 rounded-xl shadow-lg border border-gray-200 my-6">
        <header class="text-center mb-10">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">Laporan Laba Rugi</h1>
            <div class="text-sm text-gray-600">
                Order #: <strong class="text-gray-700">{{ $order->number }}</strong> | Event: <strong
                    class="text-gray-700">{{ $order->prospect?->name_event ?? 'N/A' }}</strong> <br>
                Tanggal Generate: {{ $generatedDate }}
            </div>
            {{-- Tombol Download PDF --}}
            <div class="mt-6">
                {{-- IMPROVEMENT: Menambahkan route yang benar untuk download PDF. Asumsinya route 'orders.profit_loss.download' akan dibuat. --}}
                <a href="{{ route('orders.profit_loss.download', $order) }}"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-5 rounded-lg transition duration-150 ease-in-out shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                    Download PDF
                </a>
            </div>
        </header>

        @php
            // Kalkulasi untuk ringkasan dan progress bar
            $totalPembayaranDiterima = $order->dataPembayaran->sum('nominal');
            $grandTotal = $order->grand_total ?? 0;
            $sisaPembayaran = $grandTotal - $totalPembayaranDiterima;
            $paymentProgress = $grandTotal > 0 ? ($totalPembayaranDiterima / $grandTotal) * 100 : 0;
            $paymentProgress = min($paymentProgress, 100); // Batasi maksimal 100%
        @endphp

        <section class="mb-10">
            <h2 class="flex items-center text-xl font-semibold text-gray-700 border-b border-gray-300 pb-2 mb-5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="ml-2">Ringkasan Keuangan</span>
            </h2>

            {{-- IMPROVEMENT: Menambahkan progress bar untuk visualisasi pembayaran --}}
            <div class="mb-6">
                <div class="flex justify-between mb-1 text-sm">
                    <span class="font-medium text-gray-600">Progress Pembayaran</span>
                    <span class="font-semibold text-blue-600">{{ number_format($paymentProgress, 1) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-blue-500 h-2.5 rounded-full" style="width: {{ $paymentProgress }}%"></div>
                </div>
            </div>

            <div class="space-y-3 text-sm md:text-base">
                <div class="flex justify-between py-2 border-b border-dashed border-gray-200">
                    <span class="text-gray-600">Total Paket Awal</span>
                    <span class="font-medium text-gray-800 whitespace-nowrap">Rp
                        {{ number_format($order->total_price ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-dashed border-gray-200">
                    <span class="text-gray-600">Total Penambahan</span>
                    <span class="font-medium text-gray-800 whitespace-nowrap">Rp
                        {{ number_format($order->penambahan ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-dashed border-gray-200">
                    <span class="text-gray-600">Total Promo</span>
                    <span class="font-medium text-gray-800 whitespace-nowrap">Rp
                        {{ number_format($order->promo ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-dashed border-gray-200">
                    <span class="text-gray-600">Total Pengurangan</span>
                    <span class="font-medium text-gray-800 whitespace-nowrap">Rp
                        {{ number_format($order->pengurangan ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-3 border-b-2 border-gray-300 font-bold">
                    <span class="text-gray-800">Grand Total (Nilai Proyek)</span>
                    <span class="text-gray-900 whitespace-nowrap">Rp
                        {{ number_format($grandTotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-dashed border-gray-200">
                    <span class="text-gray-600">Total Pembayaran Diterima</span>
                    <span class="font-medium text-gray-800 whitespace-nowrap">Rp
                        {{ number_format($totalPembayaranDiterima, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-dashed border-gray-200">
                    <span class="text-gray-600">Sisa Pembayaran Klien</span>
                    <span
                        class="font-medium {{ $sisaPembayaran > 0 ? 'text-red-600' : 'text-green-600' }} whitespace-nowrap">Rp
                        {{ number_format($sisaPembayaran, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-dashed border-gray-200">
                    <span class="text-gray-600">Total Pengeluaran</span>
                    <span class="font-medium text-gray-800 whitespace-nowrap">Rp
                        {{ number_format($order->tot_pengeluaran ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- IMPROVEMENT: Menonjolkan hasil Laba/Rugi dalam kartu terpisah --}}
            @php $profitLoss = $order->laba_kotor ?? 0; @endphp
            <div
                class="mt-6 p-6 rounded-lg {{ $profitLoss >= 0 ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }} border">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-base font-semibold {{ $profitLoss >= 0 ? 'text-green-800' : 'text-red-800' }}">
                            Laba / Rugi Kotor</p>
                        <p class="text-xs {{ $profitLoss >= 0 ? 'text-green-600' : 'text-red-600' }}">Grand Total -
                            Total Pengeluaran</p>
                    </div>
                    <p
                        class="text-2xl font-bold whitespace-nowrap {{ $profitLoss >= 0 ? 'text-green-700' : 'text-red-700' }}">
                        Rp {{ number_format($profitLoss, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </section>

        @if ($order->dataPembayaran->count() > 0)
            <section class="mb-10">
                <h2 class="flex items-center text-xl font-semibold text-gray-700 border-b border-gray-300 pb-2 mb-5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span class="ml-2">Detail Pembayaran Diterima</span>
                </h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-100">
                            <tr>
                                <th class="p-3 text-left font-semibold text-slate-600 rounded-tl-lg">Tanggal Bayar</th>
                                <th class="p-3 text-left font-semibold text-slate-600">Metode</th>
                                <th class="p-3 text-left font-semibold text-slate-600">Keterangan</th>
                                <th class="p-3 text-right font-semibold text-slate-600 rounded-tr-lg">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->dataPembayaran as $pembayaran)
                                <tr class="border-b border-slate-200 hover:bg-slate-50">
                                    <td class="p-3 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($pembayaran->tanggal_bayar)->format('d M Y') }}</td>
                                    <td class="p-3">{{ $pembayaran->paymentMethod?->name ?? 'N/A' }}</td>
                                    <td class="p-3">{{ $pembayaran->keterangan ?? '-' }}</td>
                                    <td class="p-3 text-right whitespace-nowrap">Rp
                                        {{ number_format($pembayaran->nominal ?? 0, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @else
            <p class="text-center text-gray-500 italic my-6">Belum ada data pembayaran diterima.</p>
        @endif

        @if ($order->expenses->count() > 0)
            <section>
                <h2 class="flex items-center text-xl font-semibold text-gray-700 border-b border-gray-300 pb-2 mb-5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="ml-2">Detail Pengeluaran</span>
                </h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-100">
                            <tr>
                                <th class="p-3 text-left font-semibold text-slate-600 rounded-tl-lg">Tanggal</th>
                                <th class="p-3 text-left font-semibold text-slate-600">Vendor</th>
                                <th class="p-3 text-left font-semibold text-slate-600">No. ND</th>
                                <th class="p-3 text-left font-semibold text-slate-600">Keterangan</th>
                                <th class="p-3 text-right font-semibold text-slate-600 rounded-tr-lg">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->expenses as $expense)
                                <tr class="border-b border-slate-200 hover:bg-slate-50">
                                    <td class="p-3 whitespace-nowrap">
                                        {{ $expense->date_expense ? \Carbon\Carbon::parse($expense->date_expense)->format('d M Y') : '-' }}
                                    </td>
                                    <td class="p-3">{{ $expense->vendor?->name ?? 'N/A' }}</td>
                                    <td class="p-3 whitespace-nowrap">
                                        {{ $expense->no_nd ? 'ND-0' . $expense->no_nd : '-' }}</td>
                                    <td class="p-3">{{ $expense->note ?? '-' }}</td>
                                    <td class="p-3 text-right whitespace-nowrap">Rp
                                        {{ number_format($expense->amount ?? 0, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @else
            <p class="text-center text-gray-500 italic my-6">Belum ada data pengeluaran untuk order ini.</p>
        @endif
    </div>
</body>

</html>
