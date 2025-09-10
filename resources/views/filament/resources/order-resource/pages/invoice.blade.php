<x-filament-panels::page>

    <link rel="stylesheet" href="{{ asset('assets/invoice/invoice.css') }}">

    <div class="bg-white shadow-m border border-gray-200 rounded-xl p-4 sm:p-6 lg:p-8 ring-gray-100">
        <!-- Invoice Header -->
        <div class="flex justify-between items-center border-b pb-4">
            <div>
                <h1 class="font-bold text-gray-800 text-sm sm:text-base">DETAILS #{{ $order->id }}</h1>
                <p class="text-gray-600 text-sm sm:text-base">Date: {{ $order->created_at->format('d M Y') }}
                </p>
            </div>
            <div>
                <img src="{{ asset(config('invoice.logo', 'images/logo.png')) }}" alt="Company Logo"
                    class="h-10 w-auto mr-4">
            </div>
        </div>

        <!-- Download Buttons -->
        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('invoice.download', ['order' => $order]) }}" target="_blank"
                class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Download Invoice
            </a>
        </div>

        @php
            $grandTotal = $order->grand_total ?? 0;
            $totalPaid = $order->bayar ?? 0;
            $paymentProgress = $grandTotal > 0 ? ($totalPaid / $grandTotal) * 100 : 0;
            $paymentProgress = min($paymentProgress, 100);
        @endphp

        <!-- Billing Information -->
        <div class="billing-info text-sm sm:text-base">
            {{-- <div class="mt-6 grid grid-cols-2 gap-4 text-sm"> --}}
            <div>
                <h2 class="text-gray-700 font-bold mb-2">Billed To :</h2>
                <p class="text-gray-600">Event :
                    {{ $order->prospect->name_event ?? 'N/A' }}</p>
                <p class="text-gray-600">Name Nama : CPP_{{ $order->prospect->name_cpp }} &
                    CPW_{{ $order->prospect->name_cpw }}</p>
                <p class="text-gray-600">Alamat :
                    {{ ucwords(strtolower($order->prospect->address ?? 'N/A')) }}
                </p>
                <p class="text-gray-600">No Tlp :
                    +62{{ $order->prospect->phone ?? 'N/A' }}</p>
                <p class="text-gray-600">Venue :
                    {{ $order->prospect->venue ?? 'N/A' }} /
                    {{ $order->pax ?? 'N/A' }}
                    Pax</p>
                <p class="text-gray-600">Account Manager :
                    {{ $order->employee->name ?? 'N/A' }}</p>
            </div>
            <div>
                <h2 class="text-sm font-semibold mb-2">Invoice Information :</h2>
                <p class="text-gray-600">Invoice Date : {{ now()->format('d F Y') }}</p>
                <p class="text-gray-600">Due Date :
                    {{ now()->addDays(30)->format('d F Y') }}</p>
                <p class="status-bayar">Status Pembayaran :
                    @if ($order->is_paid)
                        <span class="text-green-600 font-semibold">Paid</span>
                    @else
                        <span class="text-red-600 font-semibold">Unpaid</span>
                    @endif
                </p>
                <p class="text-gray-600">Tgl Lamaran :
                    {{ $order->prospect->date_lamaran ? \Carbon\Carbon::parse($order->prospect->date_lamaran)->format('d F Y') : '-' }}
                </p>
                <p class="text-gray-600">Tgl Akad :
                    {{ $order->prospect->date_akad ? \Carbon\Carbon::parse($order->prospect->date_akad)->format('d F Y') : '-' }}
                </p>
                <p class="text-gray-600">Tgl Resepsi:
                    {{ $order->prospect->date_resepsi ? \Carbon\Carbon::parse($order->prospect->date_resepsi)->format('d F Y') : '-' }}
                </p>
            </div>
        </div>

        <div class="mt-6 mb-10">
            <div class="col-12 overflow-x-auto">
                <table class="detail-tagihan-table w-full text-sm sm:text-base">
                    <thead>
                        <tr>
                            <th colspan="2" class="bg-gray-100 text-left px-4 py-2 font-semibold text-gray-700">
                                Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="px-4 py-2 border-b border-gray-200">Total Paket Awal</td>
                            <td class="text-right px-4 py-2 border-b border-gray-200">Rp
                                {{ number_format($order->total_price, 0, ',', '.') }}
                            </td>
                        </tr>

                        @if ($order->promo > 0)
                            <tr>
                                <td class="px-4 py-2 border-b border-gray-200">Diskon</td>
                                <td class="text-right px-4 py-2 border-b border-gray-200">- Rp
                                    {{ number_format($order->promo, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endif

                        @if ($order->penambahan > 0)
                            <tr>
                                <td class="px-4 py-2 border-b border-gray-200">Penambahan</td>
                                <td class="text-right px-4 py-2 border-b border-gray-200">Rp
                                    {{ number_format($order->penambahan, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endif

                        @if ($order->pengurangan > 0)
                            <tr>
                                <td class="px-4 py-2 border-b border-gray-200">Pengurangan</td>
                                <td class="text-right px-4 py-2 border-b border-gray-200">Rp
                                    {{ number_format($order->pengurangan, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endif

                        <tr>
                            <td class="font-semibold px-4 py-2 border-b border-gray-200">Grand Total</td>
                            <td class="text-right font-semibold px-4 py-2 border-b border-gray-200">Rp
                                {{ number_format($order->grand_total, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2 border-b border-gray-200">Sudah Dibayar</td>
                            <td class="text-right px-4 py-2 border-b border-gray-200">Rp
                                {{ number_format($order->bayar, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2 border-b border-gray-200">Total Pembayaran Vendor</td>
                            <td class="text-right px-4 py-2 border-b border-gray-200">Rp
                                @php
                                    $totalVendor = $order->expenses()->sum('amount');
                                @endphp
                                {{ number_format($totalVendor, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr class="total">
                            <td class="font-semibold px-4 py-2 border-b border-gray-200">Sisa Tagihan (Balance
                                Due)
                            </td>
                            <td class="text-right font-semibold px-4 py-2 border-b border-gray-200"><strong>Rp
                                    {{ number_format($order->sisa, 0, ',', '.') }}</strong>
                            </td>
                        </tr>
                    </tbody>
                </table>

                @php $profitLoss = $order->laba_kotor ?? 0; @endphp
                <div class="profit-loss-card {{ $profitLoss >= 0 ? 'is-profit' : 'is-loss' }}">
                    <div class="profit-loss-card-content">
                        <div class="profit-loss-card-details">
                            <p class="profit-loss-card-title">Laba / Rugi Kotor</p>
                            <p class="profit-loss-card-description">Grand Total - Total Pembayaran Vendor</p>
                        </div>
                        <p class="profit-loss-card-amount">
                            Rp {{ number_format($profitLoss, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Pengurangan per Produk dalam Order -->
        @php
            // For better practice, this logic should be moved to an accessor in the Order model,
            // e.g., public function getAllProductPengurangansAttribute()
            $allProductPengurangans = collect();
            if ($order->items && $order->items->count() > 0) {
                foreach ($order->items as $orderItem) {
                    if ($orderItem->product && $orderItem->product->pengurangans->count() > 0) {
                        foreach ($orderItem->product->pengurangans as $pengurangan) {
                            // Menambahkan nama produk ke objek pengurangan untuk referensi
                            $pengurangan->product_name = $orderItem->product->name;
                            $allProductPengurangans->push($pengurangan);
                        }
                    }
                }
            }
        @endphp

        <div class="mt-8 pt-10 mb-10">
            <h3 class="section-header">
                <svg xmlns="http://www.w3.org/2000/svg" class="section-header-icon" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="section-header-content">
                    <span class="section-header-title">Rincian Item Pengurangan Produk</span>
                    <p class="section-description">Menampilkan rincian item yang menjadi faktor pengurang dari total
                        harga paket produk.</p>
                </div>
            </h3>
            <div class="overflow-x-auto">
                <table class="item-pengurangan-table w-full text-sm sm:text-base">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="bg-gray-100 px-4 py-2 text-center w-10 text-gray-700 font-medium">No</th>
                            <th class="bg-gray-100 px-4 py-2 text-left text-gray-700 font-medium">Deskripsi
                                Pengurangan
                            </th>
                            <th class="bg-gray-100 px-4 py-2 text-right text-gray-700 font-medium w-2/5">Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allProductPengurangans as $index => $itemPengurangan)
                            <tr>
                                <td class="text-center px-4 py-2 border-b border-gray-200">{{ $index + 1 }}</td>
                                <td class="px-4 py-2 border-b border-gray-200">
                                    <div>
                                        {{ ucwords(strtolower($itemPengurangan->description ?? 'N/A')) }}
                                    </div>
                                    @if ($itemPengurangan->notes)
                                        <div class="ml-7 text-gray-600">
                                            {!! strip_tags($itemPengurangan->notes, '<li><strong><em><ul><br><span><div>') !!}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-right px-4 py-2 border-b border-gray-200">Rp
                                    {{ number_format($itemPengurangan->amount ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3"
                                    class="text-center px-4 py-3 border-b border-gray-200 text-gray-500 italic">
                                    Tidak ada item pengurangan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment Progress Bar -->
        <div class="progress-bar-container">
            <div class="progress-bar-header">
                <span class="progress-bar-label">Progress Pembayaran</span>
                <span class="progress-bar-percentage">{{ number_format($paymentProgress, 1) }}%</span>
            </div>
            <div class="progress-bar-track">
                <div class="progress-bar-fill" style="width: {{ $paymentProgress }}%"></div>
            </div>
        </div>

        <!-- Payment History -->
        <div class="mt-8">
            <h3 class="section-header">
                <svg xmlns="http://www.w3.org/2000/svg" class="section-header-icon" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <div class="section-header-content">
                    <span class="section-header-title">Payment History</span>
                    <p class="section-description">Riwayat semua pembayaran yang telah diterima dari klien untuk
                        invoice ini.</p>
                </div>
            </h3>
            <div class="overflow-x-auto">
                <table class="payment-history-table w-full text-sm sm:text-base">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="bg-gray-100 px-4 py-2 text-left text-gray-700 font-medium">Date</th>
                            <th class="bg-gray-100 px-4 py-2 text-right text-gray-700 font-medium">Amount</th>
                            <th class="bg-gray-100 px-4 py-2 text-left text-gray-700 font-medium">Payment Method
                            </th>
                            <th class="bg-gray-100 px-4 py-2 text-left text-gray-700 font-medium">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($order->dataPembayaran as $payment)
                            <tr>
                                <td class="px-4 py-2 border-b border-gray-200">
                                    {{ \Carbon\Carbon::parse($payment->tgl_bayar)->format('d F Y') }}
                                </td>
                                <td class="text-right px-4 py-2 border-b border-gray-200">Rp
                                    {{ number_format($payment->nominal, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-2 border-b border-gray-200">
                                    {{ $payment->paymentMethod->name ?? 'N/A' }}</td>
                                <td class="px-4 py-2 border-b border-gray-200">{{ $payment->keterangan }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center px-4 py-3 text-gray-500 italic">
                                    No payment history available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pembayaran Vendor -->
        <div class="mt-8">
            <h3 class="section-header">
                <svg xmlns="http://www.w3.org/2000/svg" class="section-header-icon" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <div class="section-header-content">
                    <span class="section-header-title">Pembayaran Vendor</span>
                    <p class="section-description">Rincian semua pengeluaran yang telah dibayarkan kepada vendor
                        terkait proyek ini.</p>
                </div>
            </h3>
            <div class="overflow-x-auto">
                <table class="vendor-payment-table w-full text-sm sm:text-base">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="bg-gray-100 px-4 py-2 text-left text-gray-700 font-medium">Tgl</th>
                            <th class="bg-gray-100 px-4 py-2 text-left text-gray-700 font-medium">Vendor</th>
                            <th class="bg-gray-100 px-4 py-2 text-left text-gray-700 font-medium">Keterangan</th>
                            <th class="bg-gray-100 px-4 py-2 text-left text-gray-700 font-medium">No ND</th>
                            <th class="bg-gray-100 px-4 py-2 text-right text-gray-700 font-medium">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $allExpenses = $order->expenses()->latest('date_expense')->get();
                            $visibleLimit = 5; // Jumlah item yang terlihat secara default
                        @endphp
                        @forelse($allExpenses as $expense)
                            <tr class="vendor-expense-row @if ($loop->iteration > $visibleLimit) hidden @endif">
                                <td class="px-4 py-2 border-b border-gray-200">
                                    {{ $expense->date_expense ? \Carbon\Carbon::parse($expense->date_expense)->format('d M Y') : '-' }}
                                </td>
                                <td class="px-4 py-2 border-b border-gray-200">
                                    {{ $expense->vendor->name ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-2 border-b border-gray-200">
                                    {{ ucwords(strtolower($expense->note ?? 'N/A')) }}
                                </td>
                                <td class="px-4 py-2 border-b border-gray-200">
                                    {{ $expense->no_nd ? 'ND-0' . $expense->no_nd : '-' }}
                                </td>
                                <td class="text-right px-4 py-2 border-b border-gray-200">Rp
                                    {{ number_format($expense->amount ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center px-4 py-3 text-gray-500 italic">
                                    Tidak ada data pembayaran vendor.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Tombol "Show More" --}}
            @if ($allExpenses->count() > $visibleLimit)
                <div class="flex justify-center">
                    <button id="toggle-vendor-expenses" class="show-more-button">
                        Tampilkan {{ $allExpenses->count() - $visibleLimit }} Lainnya
                    </button>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButton = document.getElementById('toggle-vendor-expenses');
            const rows = document.querySelectorAll('tr.vendor-expense-row');
            const visibleLimit = {{ $visibleLimit }};

            if (toggleButton) {
                toggleButton.addEventListener('click', function() {
                    let isShowingAll = this.dataset.showingAll === 'true';

                    rows.forEach((row, index) => {
                        if (index >= visibleLimit) {
                            row.classList.toggle('hidden');
                        }
                    });

                    // Update button text and state
                    isShowingAll = !isShowingAll;
                    this.dataset.showingAll = isShowingAll;
                    this.textContent = isShowingAll ? 'Tampilkan Lebih Sedikit' :
                        'Tampilkan {{ $allExpenses->count() - $visibleLimit }} Lainnya';
                });
            }
        });
    </script>
</x-filament-panels::page>
