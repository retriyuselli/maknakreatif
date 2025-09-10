@extends('layouts.app')

@section('title', 'Manajemen Vendor Internal')

@section('content')
    <div class="min-h-screen bg-gray-50">
        <!-- Header Navigation -->
        @include('front.header')

        <!-- Hero Section -->
        <section class="relative bg-gradient-to-br from-trakteer-dark via-trakteer-red to-trakteer-dark text-white py-24">
            <div class="absolute inset-0 bg-black opacity-30"></div>
            <!-- Animated Background Particles -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute w-96 h-96 bg-trakteer-yellow opacity-10 rounded-full -top-48 -left-48 animate-pulse"></div>
                <div class="absolute w-64 h-64 bg-trakteer-light-blue opacity-20 rounded-full -bottom-32 -right-32 animate-bounce"></div>
                <div class="absolute w-32 h-32 bg-trakteer-yellow opacity-15 rounded-full top-1/2 left-1/4 animate-ping"></div>
            </div>
            
            <div class="relative container mx-auto px-6">
                <div class="text-center">
                    <h1 class="text-5xl md:text-7xl font-bold mb-6 bg-gradient-to-r from-white to-blue-100 bg-clip-text text-transparent">
                        MANAJEMEN VENDOR
                    </h1>
                    <p class="text-xl md:text-2xl mb-8 text-blue-100 max-w-3xl mx-auto leading-relaxed">
                        Platform terpadu untuk mengelola ekosistem vendor wedding organizer terbaik di Indonesia
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <button class="bg-trakteer-yellow text-trakteer-dark px-6 py-3 rounded-xl font-semibold hover:bg-yellow-400 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105"
                            onclick="addNewVendor()">
                            ➕ Tambah Vendor Baru
                        </button>
                        <button onclick="exportVendors()"
                            class="border-2 border-white text-white hover:bg-white hover:text-trakteer-dark font-bold py-4 px-8 rounded-xl transition-all duration-300">
                            <span class="flex items-center justify-center space-x-2">
                                <span>📊</span>
                                <span>Export Data</span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Scroll Indicator -->
            <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
                <div class="w-6 h-10 border-2 border-white rounded-full flex justify-center">
                    <div class="w-1 h-3 bg-white rounded-full mt-2 animate-pulse"></div>
                </div>
            </div>
        </section>

        <!-- Statistics Section -->
        <section class="py-12 bg-white relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-50 to-purple-50 opacity-50"></div>
            <div class="relative container mx-auto px-6">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">📊 Statistik Vendor</h2>
                    <p class="text-gray-600 max-w-2xl mx-auto">Dashboard analytics untuk memantau performa dan perkembangan vendor wedding organizer</p>
                </div>

                <!-- Main Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Total Vendors -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-trakteer-red hover:shadow-xl transition-shadow">
                        <div class="flex items-center">
                            <div class="p-3 bg-red-100 rounded-full">
                                <span class="text-2xl">🏪</span>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm font-medium">Total Vendor</h3>
                                <p class="text-2xl font-bold text-trakteer-red">{{ $stats['total'] }}</p>
                                <p class="text-xs text-gray-400">Vendor terdaftar</p>
                            </div>
                        </div>
                    </div>

                    <!-- Active Vendors -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500 hover:shadow-xl transition-shadow">
                        <div class="flex items-center">
                            <div class="p-3 bg-green-100 rounded-full">
                                <span class="text-2xl">✅</span>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm font-medium">Vendor Aktif</h3>
                                <p class="text-2xl font-bold text-green-600">{{ $stats['active'] }}</p>
                                <p class="text-xs text-gray-400">Status vendor</p>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-trakteer-yellow hover:shadow-xl transition-shadow">
                        <div class="flex items-center">
                            <div class="p-3 bg-yellow-100 rounded-full">
                                <span class="text-2xl">💰</span>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm font-medium">Revenue {{ $stats['current_year'] }}</h3>
                                <p class="text-2xl font-bold text-trakteer-yellow">{{ $stats['revenue'] }}</p>
                                <p class="text-xs text-gray-400">Tahun berjalan</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Stats Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Profit Estimasi -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-trakteer-light-blue hover:shadow-xl transition-shadow">
                        <div class="flex items-center">
                            <div class="p-3 bg-blue-100 rounded-full">
                                <span class="text-2xl">📈</span>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm font-medium">Profit {{ $stats['current_year'] }}</h3>
                                <p class="text-2xl font-bold text-blue-600">{{ $stats['profit'] }}</p>
                                <p class="text-xs text-gray-400">Revenue - Cost (tahun ini)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Average Price -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500 hover:shadow-xl transition-shadow">
                        <div class="flex items-center">
                            <div class="p-3 bg-purple-100 rounded-full">
                                <span class="text-2xl">💱</span>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm font-medium">Rata-rata Harga</h3>
                                <p class="text-2xl font-bold text-purple-600">{{ $stats['average_price'] }}</p>
                                <p class="text-xs text-gray-400">Per vendor publish</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Filter & Search Section -->
        <section class="py-8 bg-gray-50">
            <div class="container mx-auto px-6">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <form method="GET" action="{{ route('vendor') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Hidden input untuk mempertahankan per_page -->
                        <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                        
                        <!-- Search Bar -->
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                placeholder="🔍 Cari vendor..."
                                class="w-full pl-4 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-trakteer-red focus:border-transparent">
                        </div>

                        <!-- Category Filter -->
                        <div class="relative">
                            <select name="category" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-trakteer-red focus:border-transparent appearance-none bg-white">
                                <option value="">📂 Semua Kategori</option>
                                @if(isset($categories))
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="relative">
                            <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-trakteer-red focus:border-transparent appearance-none bg-white">
                                <option value="">⚙️ Semua Status</option>
                                <option value="vendor" {{ request('status') == 'vendor' ? 'selected' : '' }}>🏪 Vendor</option>
                                <option value="product" {{ request('status') == 'product' ? 'selected' : '' }}>📦 Product</option>
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2">
                            <button type="submit" class="flex-1 bg-trakteer-red hover:bg-red-600 text-white py-3 px-4 rounded-lg transition-colors">
                                🔍 Cari
                            </button>
                            <a href="{{ route('vendor') }}?per_page={{ request('per_page', 10) }}" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-3 px-4 rounded-lg transition-colors text-center">
                                🔄 Reset
                            </a>
                        </div>
                    </form>

                    <!-- Filter Summary -->
                    @if(request()->hasAny(['search', 'category', 'status']))
                        <div class="mt-4 p-3 bg-blue-50 rounded-lg border-l-4 border-blue-400">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-blue-700">
                                    <strong>Filter aktif:</strong>
                                    @if(request('search'))
                                        <span class="ml-2 px-2 py-1 bg-blue-200 rounded text-xs">Pencarian: "{{ request('search') }}"</span>
                                    @endif
                                    @if(request('category'))
                                        <span class="ml-2 px-2 py-1 bg-blue-200 rounded text-xs">Kategori: {{ $categories->find(request('category'))->name ?? 'Unknown' }}</span>
                                    @endif
                                    @if(request('status'))
                                        <span class="ml-2 px-2 py-1 bg-blue-200 rounded text-xs">Status: {{ ucfirst(request('status')) }}</span>
                                    @endif
                                </div>
                                <a href="{{ route('vendor') }}?per_page={{ request('per_page', 10) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Hapus semua filter
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <!-- Vendor Table Section -->
        <section class="py-8">
            <div class="container mx-auto px-6">
                <!-- Pagination Controls -->
                <div class="bg-white rounded-lg shadow-lg p-4 mb-6">
                    <div class="flex justify-between items-center flex-wrap gap-4">
                        <div class="flex items-center space-x-4">
                            <!-- Per Page Selector -->
                            <div class="flex items-center space-x-2">
                                <label class="text-sm text-gray-600 font-medium">Tampilkan:</label>
                                <select onchange="changePerPage(this.value)" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-trakteer-red bg-white">
                                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                </select>
                                <span class="text-sm text-gray-600">data per halaman</span>
                            </div>
                            
                            <!-- Info jumlah data -->
                            @if(isset($vendors) && method_exists($vendors, 'total'))
                            <div class="text-sm text-gray-600 bg-gray-100 px-3 py-1 rounded-full">
                                Menampilkan {{ $vendors->firstItem() ?? 0 }} - {{ $vendors->lastItem() ?? 0 }} dari {{ $vendors->total() }} data
                            </div>
                            @endif
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="flex items-center space-x-2">
                            <button onclick="exportVendors()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md text-sm transition-colors">
                                📊 Export
                            </button>
                            <button onclick="addNewVendor()" class="bg-trakteer-yellow hover:bg-yellow-500 text-trakteer-dark px-4 py-2 rounded-md text-sm font-medium transition-colors">
                                ➕ Tambah Vendor
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table Container -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <!-- Table Header -->
                    <div class="bg-trakteer-dark text-white px-6 py-4">
                        <h2 class="text-xl font-bold">📋 Daftar Vendor</h2>
                    </div>

                    <!-- Table Content -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-trakteer-red text-white">
                                <tr>
                                    <th class="px-6 py-4 text-left">Nama Vendor</th>
                                    <th class="px-6 py-4 text-left">Kategori</th>
                                    <th class="px-6 py-4 text-left">PIC</th>
                                    <th class="px-6 py-4 text-left">Harga</th>
                                    <th class="px-6 py-4 text-left">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($vendors as $vendor)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div>
                                            <div class="font-medium text-sm text-gray-900">{{ ucfirst(strtolower($vendor->name)) }}</div>
                                            <div class="text-sm text-gray-500">{{ $vendor->phone }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                            {{ $vendor->category->name ?? 'Tidak Berkategori' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>
                                            <div class="text-sm font-medium">{{ $vendor->pic_name ?? '-' }}</div>
                                            <div class="text-sm text-gray-500">{{ $vendor->status ?? '-' }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-green-600">
                                            Rp {{ number_format($vendor->harga_publish ?? 0, 0, ',', '.') }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Cost: Rp {{ number_format($vendor->harga_vendor ?? 0, 0, ',', '.') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex space-x-2">
                                            <button onclick="viewVendorDetail({{ $vendor->id }})" 
                                                class="bg-trakteer-light-blue hover:bg-blue-400 text-white px-3 py-1 rounded text-sm font-medium transition-colors">
                                                👁️ Lihat
                                            </button>
                                            <a href="/admin/vendors/{{ $vendor->id }}/edit" 
                                                class="bg-trakteer-yellow hover:bg-yellow-500 text-trakteer-dark px-3 py-1 rounded text-sm font-medium transition-colors">
                                                ✏️ Edit
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <div class="text-6xl mb-4">📦</div>
                                            <div class="text-xl font-medium mb-2">Belum ada data vendor</div>
                                            <div class="text-sm mb-4">Silakan tambahkan vendor pertama Anda</div>
                                            <button onclick="addNewVendor()" class="bg-trakteer-yellow hover:bg-yellow-500 text-trakteer-dark px-6 py-2 rounded-lg font-medium transition-colors">
                                                ➕ Tambah Vendor Sekarang
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination Section -->
                @if(isset($vendors) && method_exists($vendors, 'hasPages'))
                    @if($vendors->hasPages())
                    <div class="mt-6 bg-white rounded-lg shadow-lg p-6">
                        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                            <div class="text-sm text-gray-600">
                                Menampilkan {{ $vendors->firstItem() }} sampai {{ $vendors->lastItem() }} dari {{ $vendors->total() }} hasil
                            </div>
                            <div class="flex items-center">
                                {{ $vendors->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="mt-6 bg-white rounded-lg shadow-lg p-4">
                        <div class="text-center text-gray-600">
                            @if(method_exists($vendors, 'total'))
                                Total {{ $vendors->total() }} data vendor (semua ditampilkan dalam satu halaman)
                            @else
                                Semua data vendor ditampilkan
                            @endif
                        </div>
                    </div>
                    @endif
                @endif
            </div>
        </section>
    </div>

    <!-- JavaScript Functions -->
    <script>
        // Function untuk mengubah per page
        function changePerPage(perPage) {
            const url = new URL(window.location);
            url.searchParams.set('per_page', perPage);
            url.searchParams.set('page', 1); // Reset ke halaman pertama
            window.location.href = url.toString();
        }

        // Function untuk menambah vendor baru
        function addNewVendor() {
            window.location.href = '/admin/vendors/create';
        }

        // Function untuk export vendors
        function exportVendors() {
            // Implementasi export - bisa redirect ke route export atau download langsung
            window.location.href = '/admin/vendors/export';
        }

        // Function untuk melihat detail vendor
        function viewVendorDetail(vendorId) {
            // Get vendor data from current page
            @if(isset($vendors) && method_exists($vendors, 'items'))
            const vendors = @json($vendors->items());
            @else
            const vendors = @json($vendors ?? []);
            @endif
            
            const selectedVendor = vendors.find(v => v.id === vendorId);
            
            if (selectedVendor) {
                showVendorModal(selectedVendor);
            } else {
                // Fallback: redirect to vendor detail page
                window.location.href = `/admin/vendors/${vendorId}`;
            }
        }

        // Function untuk menampilkan modal detail vendor
        function showVendorModal(vendor) {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';
            modal.innerHTML = `
                <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                    <div class="p-6">
                        <!-- Modal Header -->
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-800">${vendor.name}</h3>
                                <p class="text-gray-600">${vendor.slug || ''}</p>
                            </div>
                            <button onclick="closeVendorModal()" class="text-gray-400 hover:text-gray-600 text-2xl">
                                ×
                            </button>
                        </div>

                        <!-- Modal Content -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Informasi Kontak -->
                            <div>
                                <h4 class="text-lg font-semibold text-gray-800 mb-3">Informasi Kontak</h4>
                                <div class="space-y-2">
                                    <div>
                                        <label class="text-sm font-medium text-gray-600">PIC:</label>
                                        <p class="text-gray-800">${vendor.pic_name || '-'}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-600">Telepon:</label>
                                        <p class="text-gray-800">${vendor.phone || '-'}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-600">Email:</label>
                                        <p class="text-gray-800">${vendor.email || '-'}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-600">Status:</label>
                                        <span class="px-2 py-1 rounded text-sm font-medium ${vendor.status === 'vendor' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'}">
                                            ${vendor.status === 'vendor' ? 'Vendor' : 'Product'}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Informasi Harga -->
                            <div>
                                <h4 class="text-lg font-semibold text-gray-800 mb-3">Informasi Harga</h4>
                                <div class="space-y-3">
                                    <div class="bg-green-50 p-3 rounded-lg">
                                        <label class="text-sm font-medium text-green-600">Harga Publish</label>
                                        <p class="text-xl font-bold text-green-700">Rp ${vendor.harga_publish ? new Intl.NumberFormat('id-ID').format(vendor.harga_publish) : '0'}</p>
                                    </div>
                                    <div class="bg-blue-50 p-3 rounded-lg">
                                        <label class="text-sm font-medium text-blue-600">Harga Vendor</label>
                                        <p class="text-xl font-bold text-blue-700">Rp ${vendor.harga_vendor ? new Intl.NumberFormat('id-ID').format(vendor.harga_vendor) : '0'}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Deskripsi (jika ada) -->
                            ${vendor.description ? `
                            <div class="md:col-span-2">
                                <h4 class="text-lg font-semibold text-gray-800 mb-3">Deskripsi</h4>
                                <p class="text-gray-700 leading-relaxed">${vendor.description}</p>
                            </div>
                            ` : ''}
                        </div>

                        <!-- Modal Footer -->
                        <div class="flex justify-end space-x-3 mt-6 pt-6 border-t">
                            <button onclick="closeVendorModal()" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                                Tutup
                            </button>
                            <a href="/admin/vendors/${vendor.id}/edit" 
                                class="px-4 py-2 bg-trakteer-yellow text-trakteer-dark rounded-lg hover:bg-yellow-400 transition-colors">
                                Edit Vendor
                            </a>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Close modal when clicking outside
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeVendorModal();
                }
            });
        }

        // Function untuk menutup modal
        function closeVendorModal() {
            const modal = document.querySelector('.fixed.inset-0.bg-black.bg-opacity-50');
            if (modal) {
                modal.remove();
            }
        }

        // Close modal with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeVendorModal();
            }
        });
    </script>
@endsection
