@extends('layouts.app')

@section('title', 'Manajemen Vendor Internal')

@section('content')
    <div class="min-h-screen bg-gray-50" x-data="vendorManagement()">
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
                        <button @click="exportVendors()"
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

        <!-- Statistics Dashboard -->
        <section class="py-12 bg-white">
            <div class="container mx-auto px-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Total Vendor -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-trakteer-red hover:shadow-xl transition-shadow">
                        <div class="flex items-center">
                            <div class="p-3 bg-red-100 rounded-full">
                                <span class="text-2xl">�</span>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm font-medium">Total Vendor</h3>
                                <p class="text-3xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                                <p class="text-xs text-gray-400">Semua vendor terdaftar</p>
                            </div>
                        </div>
                    </div>

                    <!-- Vendor Aktif -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500 hover:shadow-xl transition-shadow">
                        <div class="flex items-center">
                            <div class="p-3 bg-green-100 rounded-full">
                                <span class="text-2xl">🏪</span>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm font-medium">Status Vendor</h3>
                                <p class="text-3xl font-bold text-green-600">{{ $stats['active'] }}</p>
                                <p class="text-xs text-gray-400">Tipe vendor</p>
                            </div>
                        </div>
                    </div>

                    <!-- Vendor Nonaktif -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500 hover:shadow-xl transition-shadow">
                        <div class="flex items-center">
                            <div class="p-3 bg-blue-100 rounded-full">
                                <span class="text-2xl">📦</span>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm font-medium">Status Product</h3>
                                <p class="text-3xl font-bold text-blue-600">{{ $stats['pending'] }}</p>
                                <p class="text-xs text-gray-400">Tipe product</p>
                            </div>
                        </div>
                    </div>

                    <!-- Total Revenue Estimasi -->
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
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
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-4">
                            <!-- Per Page Selector -->
                            <div class="flex items-center space-x-2">
                                <label class="text-sm text-gray-600 font-medium">Show:</label>
                                <select onchange="changePerPage(this.value)" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-trakteer-red bg-white">
                                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                </select>
                                <span class="text-sm text-gray-600">entries</span>
                            </div>
                            
                            <!-- Info jumlah data -->
                            @if(isset($vendors) && method_exists($vendors, 'total'))
                            <div class="text-sm text-gray-600 bg-gray-100 px-3 py-1 rounded-full">
                                Showing {{ $vendors->firstItem() ?? 0 }} to {{ $vendors->lastItem() ?? 0 }} of {{ $vendors->total() }} results
                            </div>
                            @endif
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="flex items-center space-x-2">
                            <button onclick="exportVendors()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md text-sm transition-colors">
                                📊 Export
                            </button>
                            <button onclick="addNewVendor()" class="bg-trakteer-yellow hover:bg-yellow-500 text-trakteer-dark px-4 py-2 rounded-md text-sm font-medium transition-colors">
                                ➕ Add Vendor
                            </button>
                        </div>
                    </div>
                </div>

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
                                    {{-- <th class="px-6 py-4 text-left">ID</th> --}}
                                    <th class="px-6 py-4 text-left">Nama Vendor</th>
                                    <th class="px-6 py-4 text-left">Kategori</th>
                                    <th class="px-6 py-4 text-left">PIC</th>
                                    <th class="px-6 py-4 text-left">Telepon</th>
                                    <th class="px-6 py-4 text-left">Harga Publish</th>
                                    <th class="px-6 py-4 text-left">Status</th>
                                    <th class="px-6 py-4 text-left">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vendors as $vendor)
                                <tr class="border-b hover:bg-gray-50">
                                    {{-- <td class="px-6 py-4 font-medium">{{ $vendor->id }}</td> --}}
                                    <td class="px-6 py-4">
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $vendor->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $vendor->slug }}</div>
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
                                            <div class="text-sm text-gray-500">{{ $vendor->phone ?? '-' }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm">{{ $vendor->phone ?? '-' }}</td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-green-600">
                                            Rp {{ number_format($vendor->harga_publish ?? 0, 0, ',', '.') }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Cost: Rp {{ number_format($vendor->harga_vendor ?? 0, 0, ',', '.') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($vendor->status == 'vendor')
                                            <span class="px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">Vendor</span>
                                        @elseif($vendor->status == 'product')
                                            <span class="px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">Product</span>
                                        @else
                                            <span class="px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">{{ ucfirst($vendor->status ?? 'Unknown') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex space-x-2">
                                            <button onclick="viewVendorDetail({{ $vendor->id }})" 
                                                class="bg-trakteer-light-blue hover:bg-blue-400 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 transform hover:scale-105 shadow-sm hover:shadow-md">
                                                <span class="flex items-center space-x-1">
                                                    <span>👁️</span>
                                                    <span>Lihat</span>
                                                </span>
                                            </button>
                                            <a href="/admin/vendors/{{ $vendor->id }}/edit" 
                                                class="bg-trakteer-yellow hover:bg-yellow-500 text-trakteer-dark px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 transform hover:scale-105 shadow-sm hover:shadow-md">
                                                <span class="flex items-center space-x-1">
                                                    <span>✏️</span>
                                                    <span>Edit</span>
                                                </span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <div class="text-4xl mb-4">📦</div>
                                            <div class="text-lg font-medium">Belum ada data vendor</div>
                                            <div class="text-sm">Silakan tambahkan vendor pertama Anda</div>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Debug Info (hapus setelah selesai testing) -->
                <div class="mt-4 p-4 bg-yellow-100 border border-yellow-400 rounded">
                    <strong>Debug Info:</strong><br>
                    - Vendors type: {{ get_class($vendors) }}<br>
                    - Has pages method: {{ method_exists($vendors, 'hasPages') ? 'Yes' : 'No' }}<br>
                    - Has pages: {{ method_exists($vendors, 'hasPages') && $vendors->hasPages() ? 'Yes' : 'No' }}<br>
                    - Total: {{ method_exists($vendors, 'total') ? $vendors->total() : 'N/A' }}<br>
                    - Per page: {{ method_exists($vendors, 'perPage') ? $vendors->perPage() : 'N/A' }}
                </div>

                <!-- Pagination Links -->
                @if(method_exists($vendors, 'hasPages') && $vendors->hasPages())
                <div class="mt-6 bg-white rounded-lg shadow-lg p-4">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-600">
                            Showing {{ $vendors->firstItem() }} to {{ $vendors->lastItem() }} of {{ $vendors->total() }} results
                        </div>
                        <div class="flex items-center space-x-1">
                            {{ $vendors->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
                @else
                <div class="mt-6 bg-white rounded-lg shadow-lg p-4">
                    <div class="text-center text-gray-600">
                        @if(method_exists($vendors, 'total'))
                            Total {{ $vendors->total() }} items (all shown on one page)
                        @else
                            All items shown
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </section>
    </div>

    <!-- JavaScript untuk handle perubahan per page -->
    <script>
        function changePerPage(perPage) {
            const url = new URL(window.location);
            url.searchParams.set('per_page', perPage);
            url.searchParams.set('page', 1); // Reset ke halaman pertama
            window.location.href = url.toString();
        }
    </script>

    <script>
        // Function untuk menambah vendor baru
        function addNewVendor() {
            // Redirect ke halaman admin untuk menambah vendor
            window.location.href = '/admin/vendors/create';
        }

        // Function untuk melihat detail vendor
        function viewVendorDetail(vendorId) {
            // Implementasi modal atau redirect ke halaman detail
            const vendors = @json($vendors->items());
            const selectedVendor = vendors.find(v => v.id === vendorId);
            
            if (selectedVendor) {
                // Buat modal detail vendor
                showVendorModal(selectedVendor);
            }
        }

        // Function untuk edit vendor
        function editVendor(vendorId) {
            // Redirect ke halaman edit atau buka modal edit
            window.location.href = `/admin/vendors/${vendorId}/edit`;
        }

        // Function untuk menampilkan modal detail vendor
        function showVendorModal(vendor) {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            modal.innerHTML = `
                <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-4 max-h-screen overflow-y-auto">
                    <!-- Modal Header -->
                    <div class="bg-trakteer-dark text-white px-6 py-4 rounded-t-xl">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xl font-bold">📋 Detail Vendor</h3>
                            <button onclick="closeVendorModal()" class="text-white hover:text-gray-300 text-2xl">×</button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Informasi Dasar -->
                            <div>
                                <h4 class="text-lg font-semibold text-gray-800 mb-3">Informasi Dasar</h4>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-sm font-medium text-gray-600">Nama Vendor</label>
                                        <p class="text-gray-900 font-medium">${vendor.name}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-600">Slug</label>
                                        <p class="text-gray-700">${vendor.slug || '-'}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-600">Kategori</label>
                                        <p class="text-gray-700">${vendor.category?.name || 'Tidak Berkategori'}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-600">Status</label>
                                        <span class="px-2 py-1 rounded-full text-xs font-medium ${vendor.status === 'vendor' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'}">
                                            ${vendor.status === 'vendor' ? '🏪 Vendor' : '📦 Product'}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Kontak & PIC -->
                            <div>
                                <h4 class="text-lg font-semibold text-gray-800 mb-3">Kontak & PIC</h4>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-sm font-medium text-gray-600">PIC Name</label>
                                        <p class="text-gray-900">${vendor.pic_name || '-'}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-600">Telepon</label>
                                        <p class="text-gray-900">${vendor.phone || '-'}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-600">Alamat</label>
                                        <p class="text-gray-700">${vendor.address || '-'}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Informasi Harga -->
                            <div class="md:col-span-2">
                                <h4 class="text-lg font-semibold text-gray-800 mb-3">Informasi Harga</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="bg-green-50 p-4 rounded-lg">
                                        <label class="text-sm font-medium text-green-600">Harga Publish</label>
                                        <p class="text-2xl font-bold text-green-700">Rp ${vendor.harga_publish ? new Intl.NumberFormat('id-ID').format(vendor.harga_publish) : '0'}</p>
                                    </div>
                                    <div class="bg-blue-50 p-4 rounded-lg">
                                        <label class="text-sm font-medium text-blue-600">Harga Vendor</label>
                                        <p class="text-2xl font-bold text-blue-700">Rp ${vendor.harga_vendor ? new Intl.NumberFormat('id-ID').format(vendor.harga_vendor) : '0'}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Deskripsi -->
                            ${vendor.description ? `
                            <div class="md:col-span-2">
                                <h4 class="text-lg font-semibold text-gray-800 mb-3">Deskripsi</h4>
                                <p class="text-gray-700 leading-relaxed">${vendor.description}</p>
                            </div>
                            ` : ''}
                        </div>

                        <!-- Modal Footer -->
                        <div class="flex justify-end space-x-4 mt-6 pt-6 border-t">
                            <button onclick="closeVendorModal()" 
                                class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                                Tutup
                            </button>
                            <button onclick="editVendor(${vendor.id})" 
                                class="px-6 py-2 bg-trakteer-yellow text-trakteer-dark rounded-lg hover:bg-yellow-400 transition-colors">
                                Edit Vendor
                            </button>
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
