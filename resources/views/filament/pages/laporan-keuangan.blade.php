<x-filament::page>
    <div class="space-y-6">
        {{-- Filter Form --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-950/5">
            <div class="p-4 sm:p-6">
                <form wire:submit.prevent="filter" class="space-y-6">
                    {{-- Baris 1: Range Tanggal --}}
                    <div class="grid grid-cols-12 gap-4 items-start">
                        <div class="col-span-12">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Rentang
                                Tanggal</label>
                            <div class="flex items-center gap-2 space-x-2">
                                <div class="flex-1">
                                    <input type="date" id="tanggal_awal" wire:model.defer="tanggal_awal"
                                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-primary-500 focus:border-primary-500" />
                                    <span class="text-xs text-gray-500 mt-1 block">Awal</span>
                                </div>
                                <div class="flex-1">
                                    <input type="date" id="tanggal_akhir" wire:model.defer="tanggal_akhir"
                                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-primary-500 focus:border-primary-500" />
                                    <span class="text-xs text-gray-500 mt-1 block">Akhir</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Baris 2: Jenis Transaksi dan Kata Kunci --}}
                    <div class="grid grid-cols-12 gap-4 items-start">
                        {{-- Jenis Transaksi dengan Tabs --}}
                        <div class="col-span-12 sm:col-span-8">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Jenis
                                Transaksi</label>
                            <div
                                class="rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden shadow-sm">
                                {{-- Tabs Header --}}
                                <div
                                    class="flex border-b border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50">
                                    <button type="button" onclick="showTab('masuk')" id="tab-masuk"
                                        class="flex-1 px-4 py-2 text-sm font-medium text-center border-b-2 border-primary-500 text-primary-600 bg-white dark:bg-gray-800 dark:text-primary-400">
                                        Pemasukan
                                    </button>
                                    <button type="button" onclick="showTab('keluar')" id="tab-keluar"
                                        class="flex-1 px-4 py-2 text-sm font-medium text-center border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                        Pengeluaran
                                    </button>
                                </div>

                                {{-- Tab Pemasukan --}}
                                <div id="content-masuk" class="p-4 bg-white dark:bg-gray-800 space-y-3">
                                    <div class="space-y-2" x-data="{ showMasukStatus: false }">
                                        <div class="flex items-center">
                                            <input type="checkbox" id="masuk_wedding" wire:model.defer="filter_jenis"
                                                value="Masuk (Wedding)"
                                                x-on:change="showMasukStatus = $event.target.checked"
                                                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                            <label for="masuk_wedding"
                                                class="ml-2 text-sm text-gray-700 dark:text-gray-200">
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                    Wedding
                                                </span>
                                            </label>
                                        </div>
                                        {{-- Wedding Status Options --}}
                                        <div class="ml-6 bg-gray-50 dark:bg-gray-700/50 p-2 rounded-lg" x-show="showMasukStatus" x-cloak>
                                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Status:</div>
                                            <div class="flex flex-wrap gap-2">
                                                <label class="flex items-center">
                                                    <input type="checkbox" wire:model.defer="filter_status" value="done"
                                                        class="h-3 w-3 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                                    <span class="ml-1.5 text-xs font-medium bg-green-50 text-green-700 px-2 py-0.5 rounded">
                                                        Done
                                                    </span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="checkbox" wire:model.defer="filter_status" value="processing"
                                                        class="h-3 w-3 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                                    <span class="ml-1.5 text-xs font-medium bg-blue-50 text-blue-700 px-2 py-0.5 rounded">
                                                        Processing
                                                    </span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="checkbox" wire:model.defer="filter_status" value="pending"
                                                        class="h-3 w-3 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                                    <span class="ml-1.5 text-xs font-medium bg-yellow-50 text-yellow-700 px-2 py-0.5 rounded">
                                                        Pending
                                                    </span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="checkbox" wire:model.defer="filter_status" value="cancelled"
                                                        class="h-3 w-3 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                                    <span class="ml-1.5 text-xs font-medium bg-red-50 text-red-700 px-2 py-0.5 rounded">
                                                        Cancelled
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="masuk_lain" wire:model.defer="filter_jenis"
                                            value="Masuk (Lain-lain)"
                                            class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                        <label for="masuk_lain" class="ml-2 text-sm text-gray-700 dark:text-gray-200">
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                Lain-lain
                                            </span>
                                        </label>
                                    </div>
                                </div>

                                {{-- Tab Pengeluaran --}}
                                <div id="content-keluar" class="hidden p-4 bg-white dark:bg-gray-800 space-y-3">
                                    <div class="space-y-2">
                                        <div class="flex items-center">
                                            <input type="checkbox" id="keluar_wedding" wire:model.defer="filter_jenis"
                                                value="Keluar (Wedding)"
                                                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                            <label for="keluar_wedding"
                                                class="ml-2 text-sm text-gray-700 dark:text-gray-200">
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                                    Wedding
                                                </span>
                                            </label>
                                        </div>
                                        {{-- Wedding Status Options --}}
                                        <div class="ml-6 space-y-1" x-show="document.getElementById('keluar_wedding').checked">
                                            <div class="flex flex-wrap gap-2">
                                                <label class="flex items-center">
                                                    <input type="checkbox" wire:model.defer="filter_status" value="done"
                                                        class="h-3 w-3 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                                    <span class="ml-1.5 text-xs font-medium bg-green-50 text-green-700 px-2 py-0.5 rounded">
                                                        Done
                                                    </span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="checkbox" wire:model.defer="filter_status" value="processing"
                                                        class="h-3 w-3 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                                    <span class="ml-1.5 text-xs font-medium bg-blue-50 text-blue-700 px-2 py-0.5 rounded">
                                                        Processing
                                                    </span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="checkbox" wire:model.defer="filter_status" value="pending"
                                                        class="h-3 w-3 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                                    <span class="ml-1.5 text-xs font-medium bg-yellow-50 text-yellow-700 px-2 py-0.5 rounded">
                                                        Pending
                                                    </span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="checkbox" wire:model.defer="filter_status" value="cancelled"
                                                        class="h-3 w-3 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                                    <span class="ml-1.5 text-xs font-medium bg-red-50 text-red-700 px-2 py-0.5 rounded">
                                                        Cancelled
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="keluar_ops" wire:model.defer="filter_jenis"
                                            value="Keluar (Operasional)"
                                            class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                        <label for="keluar_ops" class="ml-2 text-sm text-gray-700 dark:text-gray-200">
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300">
                                                Operasional
                                            </span>
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="keluar_lain" wire:model.defer="filter_jenis"
                                            value="Keluar (Lain-lain)"
                                            class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                        <label for="keluar_lain" class="ml-2 text-sm text-gray-700 dark:text-gray-200">
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                                Lain-lain
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <small class="text-xs text-gray-500 dark:text-gray-400 mt-1 block">
                                Pilih satu atau lebih jenis transaksi dari tab Pemasukan atau Pengeluaran
                            </small>
                        </div>
                        {{-- Tab Switch Script --}}
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                // Set initial active tab
                                showTab('masuk');
                            });

                            function showTab(tabName) {
                                // Hide all content first
                                document.getElementById('content-masuk').classList.add('hidden');
                                document.getElementById('content-keluar').classList.add('hidden');

                                // Remove active state from all tabs
                                document.getElementById('tab-masuk').classList.remove('border-primary-500', 'text-primary-600', 'bg-white',
                                    'dark:bg-gray-800');
                                document.getElementById('tab-keluar').classList.remove('border-primary-500', 'text-primary-600', 'bg-white',
                                    'dark:bg-gray-800');

                                document.getElementById('tab-masuk').classList.add('border-transparent', 'text-gray-500');
                                document.getElementById('tab-keluar').classList.add('border-transparent', 'text-gray-500');

                                // Show selected content and activate tab
                                document.getElementById('content-' + tabName).classList.remove('hidden');
                                document.getElementById('tab-' + tabName).classList.remove('border-transparent', 'text-gray-500');
                                document.getElementById('tab-' + tabName).classList.add('border-primary-500', 'text-primary-600', 'bg-white',
                                    'dark:bg-gray-800');
                            }
                        </script>

                        {{-- Kata Kunci --}}
                        <div class="col-span-12 sm:col-span-3">
                            <label for="filter_keyword"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Kata
                                Kunci</label>
                            <input type="text" id="filter_keyword" wire:model.defer="filter_keyword"
                                class="block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-primary-500 focus:border-primary-500"
                                placeholder="Cari deskripsi, vendor, event..." />
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex justify-end gap-2 space-x-2 mt-4">
                            <x-filament::button type="submit" class="flex-shrink-0">
                                Filter
                            </x-filament::button>
                            <x-filament::button type="button" color="gray" wire:click="resetFilters"
                                class="flex-shrink-0">
                                Reset
                            </x-filament::button>
                            <x-filament::button type="button" color="success" wire:click="downloadPdf"
                                class="flex-shrink-0">
                                <x-heroicon-o-arrow-down-tray class="w-5 h-5" />
                            </x-filament::button>
                        </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    {{-- <div class="grid grid-cols-3 sm:grid-cols-2 lg:grid-cols-3 gap-6"> --}}
    <div class="flex flex-col-3 sm:flex-col-2 lg:flex-col-3 gap-4 items-center">
        {{-- Total Masuk --}}
        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 ring-1 ring-gray-950/5 flex items-center gap-6 w-full">
            <div
                class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center bg-green-100 dark:bg-green-500/20">
                <x-heroicon-o-arrow-down-tray class="w-6 h-6 text-green-600 dark:text-green-400" />
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Masuk</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">Rp
                    {{ number_format($total_masuk, 0, ',', '.') }}</p>
            </div>
        </div>
        {{-- Total Keluar --}}
        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 ring-1 ring-gray-950/5 flex items-center gap-6 w-full">
            <div
                class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center bg-red-100 dark:bg-red-500/20">
                <x-heroicon-o-arrow-up-tray class="w-6 h-6 text-red-600 dark:text-red-400" />
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Keluar</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">Rp
                    {{ number_format($total_keluar, 0, ',', '.') }}</p>
            </div>
        </div>
        {{-- Saldo Akhir --}}
        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 ring-1 ring-gray-950/5 flex items-center gap-6 w-full">
            <div
                class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center bg-blue-100 dark:bg-blue-500/20">
                <x-heroicon-o-wallet class="w-6 h-6 text-blue-600 dark:text-blue-400" />
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Saldo Akhir</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">Rp
                    {{ number_format($total_masuk - $total_keluar, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-950/5">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">Tanggal</th>
                    <th scope="col" class="px-6 py-3">Jenis</th>
                    <th scope="col" class="px-6 py-3">Status</th>
                    <th scope="col" class="px-6 py-3">Deskripsi</th>
                    <th scope="col" class="px-6 py-3">Vendor</th>
                    <th scope="col" class="px-6 py-3">Prospect/Event</th>
                    <th scope="col" class="px-6 py-3">Rekening</th>
                    <th scope="col" class="px-6 py-3 text-right">Jumlah</th>
                    <th scope="col" class="px-6 py-3 text-right">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transaksi as $item)
                    <tr
                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <span @class([
                                'inline-flex items-center px-2 py-1 rounded-md text-xs font-medium',
                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' => str_contains(
                                    $item->jenis,
                                    'Masuk'),
                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' => str_contains(
                                    $item->jenis,
                                    'Keluar'),
                            ])>
                                {{ $item->jenis }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if (str_contains($item->jenis, 'Wedding') && $item->order_id)
                                @php
                                    $order = App\Models\Order::find($item->order_id);
                                    $status = $order?->status;
                                @endphp
                                @if ($status)
                                    <span @class([
                                        'inline-flex items-center px-2 py-1 rounded-md text-xs font-medium',
                                        'bg-green-50 text-green-700' => $status->value === 'done',
                                        'bg-blue-50 text-blue-700' => $status->value === 'processing',
                                        'bg-yellow-50 text-yellow-700' => $status->value === 'pending',
                                        'bg-red-50 text-red-700' => $status->value === 'cancelled',
                                    ])>
                                        {{ $status->getLabel() }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs">{{ $item->deskripsi }}</td>
                        <td class="px-6 py-4">
                            @if ($item->vendor_name)
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                    {{ $item->vendor_name }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs">{{ $item->prospect_name ?? '-' }}</td>
                        <td class="px-6 py-4 text-xs ">{{ $item->payment_method_details ?? '-' }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white text-right whitespace-nowrap">
                            Rp {{ number_format($item->jumlah, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white text-right whitespace-nowrap">
                            Rp {{ number_format($item->saldo ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="flex flex-col items-center justify-center text-center py-12">
                                <div class="mb-4">
                                    <x-heroicon-o-circle-stack class="w-12 h-12 text-gray-400" />
                                </div>
                                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Tidak Ada
                                    Data
                                    Transaksi</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Coba ubah filter Anda
                                    atau
                                    tambahkan data baru.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- JavaScript untuk handle download --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Livewire.on('download-pdf', function(data) {
                window.open(data.url, '_blank');
            });
        });
    </script>

    </div> {{-- Close Summary Cards div dan main container div --}}
</x-filament::page>
