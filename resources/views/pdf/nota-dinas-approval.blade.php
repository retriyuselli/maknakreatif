<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Persetujuan - {{ $notaDinas->no_nd }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: white;
        }
        
        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .header-left h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .header-left h2 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .header-left p {
            font-size: 11px;
            color: #666;
        }
        
        .company-info {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .company-left,
        .company-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 20px;
        }
        
        .company-right {
            padding-right: 0;
            padding-left: 20px;
        }
        
        .info-section h3 {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
        }
        
        .info-section p {
            font-size: 11px;
            margin-bottom: 3px;
            color: #666;
        }
        
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .status-disetujui {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .status-diajukan {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .status-ditolak {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .section {
            margin-bottom: 20px;
        }
        
        .section h3 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        
        .grid-2 {
            display: table;
            width: 100%;
        }
        
        .grid-item {
            display: table-cell;
            width: 50%;
            padding-right: 15px;
            vertical-align: top;
        }
        
        .grid-item:last-child {
            padding-right: 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .font-bold {
            font-weight: bold;
        }
        
        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .summary-card {
            display: table-cell;
            width: 25%;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f8f9fa;
            margin-right: 10px;
        }
        
        .summary-card:last-child {
            margin-right: 0;
        }
        
        .summary-card h4 {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .summary-card p {
            font-size: 10px;
        }
        
        .approval-section {
            border-top: 2px solid #333;
            padding-top: 20px;
            margin-top: 30px;
        }
        
        .approval-grid {
            display: table;
            width: 100%;
        }
        
        .approval-item {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 0 5px;
        }
        
        .signature-space {
            height: 60px;
            border-bottom: 1px solid #333;
            margin: 15px 0;
        }
        
        .approval-item p {
            font-size: 10px;
            margin-bottom: 3px;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <h1>SURAT PERSETUJUAN PEMBAYARAN111</h1>
                <h2>{{ $notaDinas->no_nd }}</h2>
                <p>Tanggal: {{ $notaDinas->created_at->format('d F Y') }}</p>
            </div>
        </div>

        <!-- Company Info -->
        <div class="company-info">
            <div class="company-left">
                <div class="info-section">
                    <h3>Diajukan oleh:</h3>
                    <p><strong>{{ $notaDinas->pengirim->name ?? 'N/A' }}</strong></p>
                    <p>Status: 
                        <span class="status-badge status-{{ $notaDinas->status }}">
                            {{ ucfirst($notaDinas->status) }}
                        </span>
                    </p>
                </div>
            </div>
            <div class="company-right">
                <div class="info-section">
                    <h3>Informasi Nota Dinas:</h3>
                    <p><strong>Sifat:</strong> {{ $notaDinas->sifat }}</p>
                    <p><strong>Hal:</strong> {{ $notaDinas->hal }}</p>
                </div>
            </div>
        </div>

        <!-- Detail Pengeluaran -->
        <div class="section">
            <h3>Detail Pengeluaran</h3>
            <table>
                <thead>
                    <tr>
                        <th>Vendor</th>
                        <th>Keperluan</th>
                        <th>Event</th>
                        <th>Invoice</th>
                        <th class="text-right">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($details as $detail)
                        <tr>
                            <td>
                                <strong>{{ $detail->vendor->name ?? 'N/A' }}</strong>
                                @if($detail->order && $detail->order->prospect)
                                    <br><small>{{ $detail->order->prospect->name ?? '' }}</small>
                                @endif
                                @if($detail->payment_stage)
                                    <br><small style="color: #1e40af; font-weight: bold;">Tahap: {{ $detail->payment_stage }}</small>
                                @endif
                            </td>
                            <td>{{ $detail->keperluan }}</td>
                            <td>
                                {{ $detail->order && $detail->order->prospect ? $detail->order->prospect->name_event : ($detail->event ?? '-') }}
                                @if($detail->jenis_pengeluaran)
                                    <br><small style="color: #059669; font-weight: bold;">{{ ucfirst($detail->jenis_pengeluaran) }}</small>
                                @endif
                            </td>
                            <td>
                                {{ $detail->invoice_number ?? '-' }}
                                @if($detail->status_invoice)
                                    <br><small>({{ ucfirst($detail->status_invoice) }})</small>
                                @endif
                            </td>
                            <td class="text-right font-bold">
                                Rp {{ number_format($detail->jumlah_transfer, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #f8f9fa;">
                        <td colspan="4" class="text-right font-bold">Total:</td>
                        <td class="text-right font-bold">
                            Rp {{ number_format($totalJumlahTransfer, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Detail Transfer Bank -->
        <div class="section">
            <h3>Detail Transfer Bank</h3>
            <table>
                <thead>
                    <tr>
                        <th>Bank</th>
                        <th>No. Rekening</th>
                        <th>Atas Nama</th>
                        <th>Vendor</th>
                        <th class="text-right">Jumlah Transfer</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $bankGroups = $details
                            ->whereNotNull('bank_name')
                            ->groupBy(function ($item) {
                                return $item->bank_name . '|' . $item->bank_account;
                            });
                    @endphp
                    @foreach($bankGroups as $bankGroup)
                        @php $firstDetail = $bankGroup->first(); @endphp
                        <tr>
                            <td><strong>{{ $firstDetail->bank_name }}</strong></td>
                            <td style="font-family: monospace;">{{ $firstDetail->bank_account }}</td>
                            <td>{{ $firstDetail->account_holder }}</td>
                            <td>
                                {{ $firstDetail->vendor->name ?? 'N/A' }}
                                @if($bankGroup->count() > 1)
                                    <br><small>+ {{ $bankGroup->count() - 1 }} vendor lainnya</small>
                                @endif
                            </td>
                            <td class="text-right font-bold">
                                Rp {{ number_format($bankGroup->sum('jumlah_transfer'), 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #f8f9fa;">
                        <td colspan="4" class="text-right font-bold">Total Transfer:</td>
                        <td class="text-right font-bold">
                            @php
                                $totalBankTransfer = $details->whereNotNull('bank_name')->sum('jumlah_transfer');
                            @endphp
                            Rp {{ number_format($totalBankTransfer, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Signature Section -->
        <div class="approval-section">
            <h3>Persetujuan dan Tanda Tangan</h3>
            <div class="approval-grid">
                <div class="approval-item">
                    <p><strong>Admin</strong></p>
                    <div class="signature-space"></div>
                    <p><strong>{{ $notaDinas->pengirim->name ?? 'N/A' }}</strong></p>
                    <p>{{ $notaDinas->created_at->format('d/m/Y') }}</p>
                </div>
                
                <div class="approval-item">
                    <p><strong>Event Manager</strong></p>
                    <div class="signature-space"></div>
                    <p><strong>_________________</strong></p>
                    <p>Tanggal: ___________</p>
                </div>
                
                <div class="approval-item">
                    <p><strong>Finance</strong></p>
                    <div class="signature-space"></div>
                    <p><strong>{{ $notaDinas->penerima->name ?? 'Finance' }}</strong></p>
                    <p>Tanggal: ___________</p>
                </div>
                
                <div class="approval-item">
                    <p><strong>Pimpinan</strong></p>
                    <div class="signature-space"></div>
                    <p><strong>{{ $notaDinas->approver->name ?? 'Belum Disetujui' }}</strong></p>
                    <p>{{ $notaDinas->approved_at ? $notaDinas->approved_at->format('d/m/Y') : 'Tanggal: ___________' }}</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
