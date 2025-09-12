<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('assets/invoice/invoice.css') }}">

    <div class="bg-white shadow-sm border border-gray-200 rounded-xl p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div class="flex justify-between items-center border-b pb-6 mb-6">
            <div>
                <h1 class="font-bold text-gray-800 text-xl">SURAT PERSETUJUAN PEMBAYARAN</h1>
                <h2 class="font-semibold text-gray-700 text-lg mt-2">No. ND: {{ $notaDinas->no_nd }}</h2>
                <p class="text-gray-600 text-sm">Tanggal: {{ $notaDinas->created_at->format('d F Y') }}</p>
            </div>
            <div>
                <img src="{{ asset(config('invoice.logo', 'images/logo.png')) }}" alt="Company Logo" class="h-16 w-auto">
            </div>
        </div>

        <!-- Company Info -->
        <div class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">Dari:</h3>
                    <p class="text-gray-700 font-medium">{{ config('app.name', 'Makna Kreatif') }}</p>
                    <p class="text-gray-600">Wedding Organizer</p>
                    <p class="text-gray-600">Jl. Contoh No. 123, Jakarta</p>
                    <p class="text-gray-600">Telp: (021) 123-4567</p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">Diajukan oleh:</h3>
                    <p class="text-gray-700 font-medium">{{ $notaDinas->pengirim->name ?? 'N/A' }}</p>
                    <p class="text-gray-600">{{ $notaDinas->pengirim->email ?? 'N/A' }}</p>
                    <p class="text-gray-600">Status: 
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            {{ $notaDinas->status === 'disetujui' ? 'bg-green-100 text-green-800' : 
                               ($notaDinas->status === 'diajukan' ? 'bg-yellow-100 text-yellow-800' : 
                                ($notaDinas->status === 'ditolak' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                            {{ ucfirst($notaDinas->status) }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Summary Info -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                <div>
                    <p class="text-sm text-gray-600">Total Item</p>
                    <p class="text-lg font-semibold text-gray-800">{{ $details->count() }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Amount</p>
                    <p class="text-lg font-semibold text-blue-600">Rp {{ number_format($totalJumlahTransfer, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Tanggal Pengajuan</p>
                    <p class="text-lg font-semibold text-gray-800">{{ $notaDinas->created_at->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Detail Pengeluaran -->
        <div class="mb-6">
            <h3 class="font-semibold text-gray-800 text-lg mb-4">Detail Pengeluaran</h3>
            
            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keperluan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event/Kegiatan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tahap Bayar</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($details as $index => $detail)
                        <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                <div class="font-medium">{{ $detail->vendor->name ?? 'N/A' }}</div>
                                @if($detail->vendor)
                                <div class="text-xs text-gray-500">{{ $detail->vendor->pic_name ?? '' }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $detail->keperluan }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $detail->jenis_pengeluaran === 'wedding' ? 'bg-green-100 text-green-800' : 
                                       ($detail->jenis_pengeluaran === 'operasional' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ ucfirst($detail->jenis_pengeluaran) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                @if($detail->jenis_pengeluaran === 'wedding' && $detail->order)
                                    <div class="font-medium">{{ $detail->order->name }}</div>
                                    @if($detail->order->prospect)
                                    <div class="text-xs text-gray-500">{{ $detail->order->prospect->name_event }}</div>
                                    @endif
                                @else
                                    {{ $detail->event ?? '-' }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                @if($detail->jenis_pengeluaran === 'wedding')
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-orange-100 text-orange-800">
                                        {{ $detail->payment_stage ?? 'DP' }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-right font-medium">
                                Rp {{ number_format($detail->jumlah_transfer, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-100">
                        <tr>
                            <td colspan="6" class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">
                                Total Keseluruhan:
                            </td>
                            <td class="px-4 py-3 text-sm font-bold text-gray-900 text-right">
                                Rp {{ number_format($totalJumlahTransfer, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Breakdown by Jenis -->
        @if($totalByJenis->count() > 1)
        <div class="mb-6">
            <h3 class="font-semibold text-gray-800 text-lg mb-4">Breakdown Berdasarkan Jenis</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($totalByJenis as $jenis => $total)
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">{{ ucfirst($jenis) }}</p>
                            <p class="text-lg font-semibold text-gray-900">Rp {{ number_format($total, 0, ',', '.') }}</p>
                        </div>
                        <div class="text-2xl">
                            @if($jenis === 'wedding')
                                💒
                            @elseif($jenis === 'operasional')
                                🏢
                            @else
                                📋
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Bank Transfer Details -->
        <div class="mb-6">
            <h3 class="font-semibold text-gray-800 text-lg mb-4">Detail Transfer Bank</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @php
                    $bankGroups = $details->whereNotNull('bank_name')
                        ->groupBy(function($item) {
                            return $item->bank_name . '|' . $item->bank_account;
                        })
                        ->take(6);
                @endphp
                @foreach($bankGroups as $bankGroup)
                    @php $firstDetail = $bankGroup->first(); @endphp
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <h4 class="font-medium text-gray-800 mb-2">{{ $firstDetail->bank_name }}</h4>
                        <p class="text-sm text-gray-600">Rekening: <span class="font-mono">{{ $firstDetail->bank_account }}</span></p>
                        <p class="text-sm text-gray-600">A/N: {{ $firstDetail->account_holder }}</p>
                        <p class="text-sm text-gray-600 mt-2">
                            Vendor: <span class="font-medium">{{ $firstDetail->vendor->name ?? 'N/A' }}</span>
                        </p>
                        <p class="text-sm font-semibold text-blue-600 mt-2">
                            Total: Rp {{ number_format($bankGroup->sum('jumlah_transfer'), 0, ',', '.') }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Approval Section -->
        <div class="border-t pt-6">
            <h3 class="font-semibold text-gray-800 text-lg mb-6">Persetujuan</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Diajukan oleh -->
                <div class="text-center">
                    <p class="text-sm text-gray-600 mb-4">Diajukan oleh</p>
                    <div class="h-16 border-b border-gray-300 mb-2"></div>
                    <p class="text-sm font-medium text-gray-800">{{ $notaDinas->pengirim->name ?? 'N/A' }}</p>
                    <p class="text-xs text-gray-500">{{ $notaDinas->created_at->format('d/m/Y') }}</p>
                </div>

                <!-- Disetujui oleh -->
                <div class="text-center">
                    <p class="text-sm text-gray-600 mb-4">Disetujui oleh</p>
                    <div class="h-16 border-b border-gray-300 mb-2"></div>
                    <p class="text-sm font-medium text-gray-800">{{ $notaDinas->approver->name ?? 'Belum Disetujui' }}</p>
                    <p class="text-xs text-gray-500">Tanggal: {{ $notaDinas->approved_at ? $notaDinas->approved_at->format('d/m/Y') : '___________' }}</p>
                </div>

                <!-- Finance -->
                <div class="text-center">
                    <p class="text-sm text-gray-600 mb-4">Diproses oleh Finance</p>
                    <div class="h-16 border-b border-gray-300 mb-2"></div>
                    <p class="text-sm font-medium text-gray-800">{{ $notaDinas->penerima->name ?? 'Finance' }}</p>
                    <p class="text-xs text-gray-500">Tanggal: ___________</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 pt-4 border-t text-center text-xs text-gray-500">
            <p>Dokumen ini digenerate secara otomatis pada {{ now()->format('d F Y H:i') }}</p>
            <p>{{ config('app.name') }} - Wedding Organizer Management System</p>
        </div>
    </div>

    <!-- Print Styles -->
    <style>
        @media print {
            .fi-header, .fi-sidebar, .fi-topbar, .fi-main-ctn > *:not(.fi-main) {
                display: none !important;
            }
            
            .fi-main {
                padding: 0 !important;
                margin: 0 !important;
            }
            
            body {
                background: white !important;
            }
            
            .bg-white {
                background: white !important;
            }
            
            .shadow-sm, .shadow-lg {
                box-shadow: none !important;
            }
            
            .border {
                border: 1px solid #000 !important;
            }
            
            .text-blue-600 {
                color: #000 !important;
                font-weight: bold !important;
            }
        }
    </style>
</x-filament-panels::page>
