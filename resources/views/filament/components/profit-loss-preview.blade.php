<div class="p-4 bg-white">
    <style>
        .preview-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px;
            font-size: 12px;
        }
        .preview-table th, 
        .preview-table td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left; 
            vertical-align: top;
        }
        .preview-table th { 
            background-color: #f5f5f5; 
            font-weight: bold;
        }
        .preview-table .text-right { 
            text-align: right; 
        }
        .preview-table .text-center { 
            text-align: center; 
        }
        .total-row { 
            background-color: #f9f9f9; 
            font-weight: bold; 
        }
        .profit { 
            color: #28a745; 
        }
        .loss { 
            color: #dc3545; 
        }
        .section-title { 
            font-size: 16px; 
            font-weight: bold; 
            margin: 20px 0 10px 0; 
            border-bottom: 2px solid #eee; 
            padding-bottom: 5px;
        }
        .sub-title { 
            font-size: 14px; 
            font-weight: bold; 
            margin: 15px 0 10px 0; 
            color: #333;
        }
        .report-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .filter-info {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #666;
        }
    </style>

    <div class="report-header">
        <h1 style="margin: 0; font-size: 18px; color: #333;">Laporan Laba Rugi Klien</h1>
        <div class="filter-info">
            <strong>Periode Filter:</strong>
            @if($filterStartDate || $filterEndDate)
                {{ $filterStartDate ? \Carbon\Carbon::parse($filterStartDate)->format('d M Y') : 'Awal' }} -
                {{ $filterEndDate ? \Carbon\Carbon::parse($filterEndDate)->format('d M Y') : 'Akhir' }}
            @else
                Semua Order
            @endif
            | <strong>Generated:</strong> {{ $generatedDate }}
        </div>
    </div>

    <!-- Detail Order Section -->
    <div class="section-title">Detail Order</div>
    <table class="preview-table">
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 30%;">Nama Event</th>
                <th style="width: 15%;">Tgl Closing</th>
                <th class="text-right" style="width: 12%;">Total Pemasukan</th>
                <th class="text-right" style="width: 13%;">Nilai Order</th>
                <th class="text-right" style="width: 12%;">Total Pengeluaran</th>
                <th class="text-right" style="width: 13%;">Laba / Rugi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                @php
                    $totalPembayaranDiterimaOrder = $order->dataPembayaran()->sum('nominal');
                    $profitLoss = ($order->grand_total ?? 0) - ($order->expenses()->sum('amount') ?? 0);
                @endphp
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $order->prospect?->name_event ?? 'N/A' }}</td>
                    <td>{{ $order->closing_date ? \Carbon\Carbon::parse($order->closing_date)->format('d M Y') : '-' }}</td>
                    <td class="text-right">Rp {{ number_format($totalPembayaranDiterimaOrder ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($order->grand_total ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($order->expenses()->sum('amount') ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right {{ $profitLoss >= 0 ? 'profit' : 'loss' }}">
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
                <td class="text-right"><strong>Rp {{ number_format($totalIncome ?? 0, 0, ',', '.') }}</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($totalExpenses ?? 0, 0, ',', '.') }}</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($sumAllOrdersPengeluaran ?? 0, 0, ',', '.') }}</strong></td>
                <td class="text-right {{ ($netProfit ?? 0) >= 0 ? 'profit' : 'loss' }}">
                    <strong>Rp {{ number_format($netProfit ?? 0, 0, ',', '.') }}</strong>
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Detail Pengeluaran Section -->
    @if(isset($expenseOps) && $expenseOps->isNotEmpty() || isset($pengeluaranLain) && $pengeluaranLain->isNotEmpty())
        <div class="section-title">Detail Pengeluaran Operasional & Lainnya111</div>
        
        @if(isset($expenseOps) && $expenseOps->isNotEmpty())
            <div class="sub-title">Pengeluaran Operasional</div>
            <table class="preview-table">
                <thead>
                    <tr>
                        <th style="width: 8%;">Vendor</th>
                        <th style="width: 30%;">Keterangan</th>
                        <th style="width: 15%;">Tanggal</th>
                        <th style="width: 20%;">Nama Pengeluaran</th>
                        <th class="text-right" style="width: 17%;">Jumlah</th>
                        <th style="width: 10%;">No. ND</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expenseOps as $expense)
                        <tr>
                            <td class="text-center">{{ $expense->vendor->name ?? '-' }}</td>
                            <td>{{ $expense->name ?? 'N/A' }}</td>
                            <td>{{ $expense->date_expense ? \Carbon\Carbon::parse($expense->date_expense)->format('d M Y') : '-' }}</td>
                            <td>{{ $expense->name ?? 'Operasional' }}</td>
                            <td class="text-right">Rp {{ number_format($expense->amount ?? 0, 0, ',', '.') }}</td>
                            <td class="text-center">{{ $expense->no_nd ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="4" class="text-left"><strong>Sub Total Operasional:</strong></td>
                        <td class="text-right"><strong>Rp {{ number_format($totalExpenseOps ?? 0, 0, ',', '.') }}</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        @endif

        @if(isset($pengeluaranLain) && $pengeluaranLain->isNotEmpty())
            <div class="sub-title">Pengeluaran Lainnya</div>
            <table class="preview-table">
                <thead>
                    <tr>
                        <th style="width: 8%;">Vendor</th>
                        <th style="width: 30%;">Keterangan</th>
                        <th style="width: 15%;">Tanggal</th>
                        <th style="width: 20%;">Nama Pengeluaran</th>
                        <th class="text-right" style="width: 17%;">Jumlah</th>
                        <th style="width: 10%;">No. ND</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pengeluaranLain as $expense)
                        <tr>
                            <td class="text-center">{{ $expense->vendor->name ?? '-' }}</td>
                            <td>{{ $expense->name ?? 'N/A' }}</td>
                            <td>{{ $expense->date_expense ? \Carbon\Carbon::parse($expense->date_expense)->format('d M Y') : '-' }}</td>
                            <td>{{ $expense->name ?? 'Lainnya' }}</td>
                            <td class="text-right">Rp {{ number_format($expense->amount ?? 0, 0, ',', '.') }}</td>
                            <td class="text-center">{{ $expense->no_nd ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="4" class="text-left"><strong>Sub Total Lainnya:</strong></td>
                        <td class="text-right"><strong>Rp {{ number_format($totalPengeluaranLain ?? 0, 0, ',', '.') }}</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        @endif

        @php
            $grandTotalExpenses = ($totalExpenseOps ?? 0) + ($totalPengeluaranLain ?? 0);
        @endphp
        <table class="preview-table">
            <tfoot>
                <tr class="total-row" style="background-color: #e9ecef;">
                    <td colspan="4" class="text-left"><strong>TOTAL PENGELUARAN OPS & LAINNYA:</strong></td>
                    <td class="text-right"><strong>Rp {{ number_format($grandTotalExpenses, 0, ',', '.') }}</strong></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    @endif

    <!-- Summary Information -->
    <div style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
        <h4 style="margin-top: 0;">Ringkasan Laporan</h4>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <strong>Total Pemasukan:</strong> Rp {{ number_format($totalIncome ?? 0, 0, ',', '.') }}<br>
                <strong>Total Pengeluaran Wedding:</strong> Rp {{ number_format($sumAllOrdersPengeluaran ?? 0, 0, ',', '.') }}<br>
                @if(isset($grandTotalExpenses))
                    <strong>Total Pengeluaran Ops & Lain:</strong> Rp {{ number_format($grandTotalExpenses, 0, ',', '.') }}<br>
                @endif
            </div>
            <div>
                <strong>Nilai Order (Grand Total):</strong> Rp {{ number_format($totalExpenses ?? 0, 0, ',', '.') }}<br>
                <strong class="{{ ($netProfit ?? 0) >= 0 ? 'profit' : 'loss' }}">Laba Bersih Wedding:</strong> 
                <span class="{{ ($netProfit ?? 0) >= 0 ? 'profit' : 'loss' }}">Rp {{ number_format($netProfit ?? 0, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Download PDF Section -->
    <div style="margin-top: 20px; text-align: center; border-top: 1px solid #dee2e6; padding-top: 15px;">
        <a 
            href="{{ route('laporan-keuangan.download-pdf-direct') }}?startDate={{ $filterStartDate ?? '2025-10-01' }}&endDate={{ $filterEndDate ?? '2025-10-31' }}"
            target="_blank"
            style="background-color: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-size: 14px; display: inline-block; font-weight: bold;"
            onmouseover="this.style.backgroundColor='#c82333'" 
            onmouseout="this.style.backgroundColor='#dc3545'"
            onclick="this.innerHTML='⏳ Downloading PDF...'; setTimeout(() => this.innerHTML='📄 Download PDF Laporan', 3000);"
        >
            📄 Download PDF Laporan
        </a>
        
        <p style="margin-top: 10px; font-size: 12px; color: #6c757d;">
            Klik tombol di atas untuk mendownload laporan dalam format PDF
        </p>
    </div>

</div>