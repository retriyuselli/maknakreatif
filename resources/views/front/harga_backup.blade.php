@extends('front.layouts.app')

@section('title', 'Harga Paket - WOFINS')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50">
    <!-- Header Section -->
    <div class="text-center py-16 px-4">
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
            Semua Fitur Hebat Kledo
        </h1>
        <p class="text-xl text-gray-600 mb-8">
            Hanya <span class="font-bold text-blue-600">159rb/bulan</span>
        </p>
        <p class="text-gray-500 max-w-2xl mx-auto mb-8">
            Paket Terjangkau dengan harga yang sangat terjangkau mulai dari Rp. 159.000/bulan tanpa ada biaya tersembunyi!
        </p>
        
        <!-- Toggle Button -->
        <div class="flex items-center justify-center mb-4">
            <span class="text-gray-700 mr-3">Perbulanan</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer" id="yearlyToggle">
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
            <span class="text-gray-700 ml-3">Pertahun</span>
        </div>
        <p class="text-center text-blue-600 text-sm mb-12">
            Hemat <span class="font-semibold">35%</span> untuk langganan tahunan
        </p>
    </div>

    <!-- Pricing Cards -->
    <div class="max-w-5xl mx-auto px-4 pb-16">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <!-- Non Hastana Plan -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8 hover:shadow-xl transition-all duration-300">
                <div class="text-center mb-6">
                    <h3 class="text-2xl font-semibold text-gray-600 mb-2">Non Hastana</h3>
                    <div class="mb-4">
                        <span class="text-5xl font-bold text-gray-900" id="non-hastana-price">Rp 299.000</span>
                    </div>
                    <p class="text-gray-500 text-sm" id="non-hastana-period">Per event</p>
                </div>
                
                <button class="w-full py-3 px-4 bg-gray-600 text-white rounded-lg font-medium hover:bg-gray-700 transition-colors mb-6">
                    Pilih Paket Non Hastana
                </button>

                <div class="text-center mb-6">
                    <p class="text-gray-700 font-medium">Paket Standar untuk Non-Anggota</p>
                </div>
                
                <div class="space-y-3">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Manajemen Event Lengkap</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Sistem Pembayaran Terintegrasi</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Laporan Keuangan Detail</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Manajemen Vendor & Supplier</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Tracking Budget Real-time</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Dashboard Analytics</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Support Email & Chat</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Export Data Excel/PDF</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Multi-User Access (3 User)</span>
                    </div>
                </div>
            </div>

            <!-- Anggota Hastana Plan -->
            <div class="bg-white rounded-2xl shadow-lg border-2 border-blue-500 p-8 hover:shadow-xl transition-all duration-300 relative">
                <div class="absolute -top-3 left-1/2 transform -translate-x-1/2">
                    <span class="bg-blue-500 text-white px-4 py-1 rounded-full text-xs font-bold">Recommended</span>
                </div>
                <div class="absolute -top-3 right-4">
                    <span class="bg-yellow-400 text-black px-3 py-1 rounded-full text-xs font-bold">Popular</span>
                </div>
                
                <div class="text-center mb-6">
                    <h3 class="text-2xl font-semibold text-blue-600 mb-2">Anggota Hastana</h3>
                    <div class="mb-4">
                        <span class="text-5xl font-bold text-blue-600" id="hastana-price">Rp 249.000</span>
                    </div>
                    <p class="text-gray-500 text-sm" id="hastana-period">Per event</p>
                    <div class="mt-2">
                        <span class="text-green-600 font-semibold text-lg">Hemat Rp 50.000!</span>
                    </div>
                </div>
                
                <button class="w-full py-3 px-4 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors mb-6">
                    Pilih Paket Anggota Hastana
                </button>

                <div class="text-center mb-6">
                    <p class="text-gray-700 font-medium">Paket Premium untuk Anggota Hastana</p>
                </div>
                
                <div class="space-y-3">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700 font-semibold">Semua Fitur Non Hastana</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700 font-semibold">Diskon Khusus Anggota Hastana</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Priority Support 24/7</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Konsultasi Personal Wedding Planner</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Template & Tools Premium</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Akses Vendor Network Eksklusif</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Multi-User Access (5 User)</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Backup & Recovery Premium</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Pelatihan & Workshop Gratis</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
                        </svg>
                        <span class="text-sm text-gray-700">1.000 Produk</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 hover:shadow-xl transition-all duration-300">
                <div class="text-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-600 mb-2">Pro</h3>
                    <div class="mb-4">
                        <span class="text-4xl font-bold text-gray-900" id="pro-price">Rp 159.900</span>
                    </div>
                    <p class="text-gray-500 text-sm" id="pro-period">Per bulan dibayar per tahun</p>
                </div>
                
                <button class="w-full py-3 px-4 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors mb-6">
                    Coba Gratis Sekarang
                </button>

                <div class="text-center mb-6">
                    <p class="text-gray-700 font-medium">Semua Fitur Paket Gratis</p>
                </div>
                
                <div class="space-y-3">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">1 User</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">1 Gudang</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Alur Bisnis Lengkap</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Manajemen Stok</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Aset Tetap</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Inventori Multi Gudang</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">50 Laporan Keuangan & Bisnis</span>
                    </div>
                    <div class="space-y-1 text-sm text-gray-700 pl-8">
                        <p>Multi Cabang, Multi Divisi, Multi Outlet, Multi Proyek</p>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Produk Paket</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Pembatasan Hak Akses</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">File Attachment</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Auto Backup</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">1 on 1 Free Support</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">3.000 Faktur Penjualan/bulan</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">5.000 Produk</span>
                    </div>
                </div>
            </div>

            <!-- Elite Plan -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 hover:shadow-xl transition-all duration-300">
                <div class="text-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-600 mb-2">Elite</h3>
                    <div class="mb-4">
                        <span class="text-4xl font-bold text-gray-900" id="elite-price">Rp 279.900</span>
                    </div>
                    <p class="text-gray-500 text-sm" id="elite-period">Per bulan dibayar per tahun</p>
                </div>
                
                <button class="w-full py-3 px-4 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors mb-6">
                    Coba Gratis Sekarang
                </button>

                <div class="text-center mb-6">
                    <p class="text-gray-700 font-medium">Semua Fitur Paket Pro</p>
                </div>
                
                <div class="space-y-3">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">2 User</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">10 Gudang</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Produk Manufaktur Sederhana</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Konversi Satuan</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Harga Bertingkat</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Diskon Per Kontak & Per Produk</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Barcode Produk</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Advance Sales Commission</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Custom Template Invoice***</span>
                    </div>
                    <div class="space-y-1 text-sm text-gray-700 pl-8">
                        <p>Marketplace Connect (Tokopedia, Shopee, Lazada, Tiktok Shop) ***</p>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Custom Field</span>
                    </div>
                    <div class="space-y-1 text-sm text-gray-700 pl-8">
                        <p>Pembatasan Hak Akses Detail Per User</p>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">6.000 Faktur Penjualan/bulan</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">10.000 Produk</span>
                    </div>
                </div>
            </div>

            <!-- Champion Plan -->
            <div class="bg-white rounded-2xl shadow-lg border-2 border-blue-500 p-6 hover:shadow-xl transition-all duration-300 relative">
                <div class="absolute -top-3 left-1/2 transform -translate-x-1/2">
                    <span class="bg-blue-500 text-white px-4 py-1 rounded-full text-xs font-bold">Champion</span>
                </div>
                <div class="absolute -top-3 right-4">
                    <span class="bg-yellow-400 text-black px-3 py-1 rounded-full text-xs font-bold">Popular</span>
                </div>
                
                <div class="text-center mb-6">
                    <h3 class="text-lg font-semibold text-blue-600 mb-2">Champion</h3>
                    <div class="mb-4">
                        <span class="text-4xl font-bold text-blue-600" id="champion-price">Rp 399.900</span>
                    </div>
                    <p class="text-gray-500 text-sm" id="champion-period">Per bulan dibayar per tahun</p>
                </div>
                
                <button class="w-full py-3 px-4 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors mb-6">
                    Coba Gratis Sekarang
                </button>

                <div class="text-center mb-6">
                    <p class="text-gray-700 font-medium">Semua Fitur Paket Elite</p>
                </div>
                
                <div class="space-y-3">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">5 User</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">20 Gudang</span>
                    </div>
                    <div class="space-y-1 text-sm text-gray-700 pl-8">
                        <p>Produk Manufaktur Advance, Produk Serial, Produk Batch & Expiry</p>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Multi Currency</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Budgeting</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Approval</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Konsolidasi Anak Perusahaan</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">White Label</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">Offline Backup Data</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">SSO</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">8.000 Faktur/bulan</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-gray-700">15.000 Produk</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Disclaimer -->
        <div class="mt-8 text-center">
            <p class="text-gray-500 text-sm">
                *** Custom template invoice dan marketplace connect tersedia dipaket Elite ke atas dan membutuhkan pembelian addon.
            </p>
        </div>
    </div>

    <!-- Features Comparison Table -->
    <div class="max-w-7xl mx-auto px-4 pb-16">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="p-6 bg-gray-50 border-b">
                <h2 class="text-2xl font-bold text-gray-900 text-center">Detail Perbandingan Paket</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="text-left py-4 px-6 font-semibold text-gray-900">Fitur</th>
                            <th class="text-center py-4 px-6 font-semibold text-gray-900">Gratis</th>
                            <th class="text-center py-4 px-6 font-semibold text-blue-600">Pro</th>
                            <th class="text-center py-4 px-6 font-semibold text-red-600">Business</th>
                            <th class="text-center py-4 px-6 font-semibold text-yellow-600">Enterprise</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr>
                            <td class="py-4 px-6 font-medium text-gray-900">Jumlah Transaksi</td>
                            <td class="py-4 px-6 text-center text-gray-600">10 per bulan</td>
                            <td class="py-4 px-6 text-center text-blue-600">Unlimited</td>
                            <td class="py-4 px-6 text-center text-red-600">Unlimited</td>
                            <td class="py-4 px-6 text-center text-yellow-600">Unlimited</td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="py-4 px-6 font-medium text-gray-900">Jumlah User</td>
                            <td class="py-4 px-6 text-center text-gray-600">1</td>
                            <td class="py-4 px-6 text-center text-blue-600">3</td>
                            <td class="py-4 px-6 text-center text-red-600">10</td>
                            <td class="py-4 px-6 text-center text-yellow-600">Unlimited</td>
                        </tr>
                        <tr>
                            <td class="py-4 px-6 font-medium text-gray-900">Laporan Keuangan</td>
                            <td class="py-4 px-6 text-center">
                                <svg class="w-5 h-5 text-green-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <svg class="w-5 h-5 text-green-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <svg class="w-5 h-5 text-green-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <svg class="w-5 h-5 text-green-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="py-4 px-6 font-medium text-gray-900">Manajemen Stok</td>
                            <td class="py-4 px-6 text-center">
                                <svg class="w-5 h-5 text-red-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <svg class="w-5 h-5 text-green-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <svg class="w-5 h-5 text-green-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <svg class="w-5 h-5 text-green-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-4 px-6 font-medium text-gray-900">POS System</td>
                            <td class="py-4 px-6 text-center">
                                <svg class="w-5 h-5 text-red-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <svg class="w-5 h-5 text-red-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <svg class="w-5 h-5 text-green-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <svg class="w-5 h-5 text-green-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="py-4 px-6 font-medium text-gray-900">API Integration</td>
                            <td class="py-4 px-6 text-center">
                                <svg class="w-5 h-5 text-red-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <svg class="w-5 h-5 text-red-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <svg class="w-5 h-5 text-green-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <svg class="w-5 h-5 text-green-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-4 px-6 font-medium text-gray-900">Priority Support</td>
                            <td class="py-4 px-6 text-center">
                                <svg class="w-5 h-5 text-red-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <svg class="w-5 h-5 text-red-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <svg class="w-5 h-5 text-red-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <svg class="w-5 h-5 text-green-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="max-w-4xl mx-auto px-4 pb-16">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Pertanyaan yang Sering Diajukan</h2>
            <p class="text-gray-600">Temukan jawaban untuk pertanyaan umum tentang paket layanan kami</p>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-md">
                <button class="w-full text-left p-6 focus:outline-none" onclick="toggleFAQ(1)">
                    <div class="flex justify-between items-center">
                        <h3 class="font-semibold text-gray-900">Apakah ada biaya tersembunyi?</h3>
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </button>
                <div id="faq-1" class="hidden px-6 pb-6">
                    <p class="text-gray-600">Tidak ada biaya tersembunyi. Semua fitur yang tercantum dalam paket sudah termasuk dalam harga yang ditampilkan.</p>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md">
                <button class="w-full text-left p-6 focus:outline-none" onclick="toggleFAQ(2)">
                    <div class="flex justify-between items-center">
                        <h3 class="font-semibold text-gray-900">Bisakah saya upgrade paket kapan saja?</h3>
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </button>
                <div id="faq-2" class="hidden px-6 pb-6">
                    <p class="text-gray-600">Ya, Anda dapat upgrade paket kapan saja. Perubahan akan berlaku segera dan Anda hanya membayar selisih harga.</p>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md">
                <button class="w-full text-left p-6 focus:outline-none" onclick="toggleFAQ(3)">
                    <div class="flex justify-between items-center">
                        <h3 class="font-semibold text-gray-900">Apakah data saya aman?</h3>
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </button>
                <div id="faq-3" class="hidden px-6 pb-6">
                    <p class="text-gray-600">Keamanan data adalah prioritas utama kami. Kami menggunakan enkripsi SSL dan backup otomatis untuk melindungi data Anda.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFAQ(id) {
    const faq = document.getElementById('faq-' + id);
    faq.classList.toggle('hidden');
}

// Yearly/Monthly toggle functionality
document.getElementById('yearlyToggle').addEventListener('change', function() {
    const isYearly = this.checked;
    
    // Define prices
    const prices = {
        pro: {
            monthly: 'Rp 159.900',
            yearly: 'Rp 1.279.200',
            monthlyPeriod: 'Per bulan dibayar per tahun',
            yearlyPeriod: 'Per tahun (Hemat 35%)'
        },
        elite: {
            monthly: 'Rp 279.900',
            yearly: 'Rp 2.183.200',
            monthlyPeriod: 'Per bulan dibayar per tahun',
            yearlyPeriod: 'Per tahun (Hemat 35%)'
        },
        champion: {
            monthly: 'Rp 399.900',
            yearly: 'Rp 3.119.200',
            monthlyPeriod: 'Per bulan dibayar per tahun',
            yearlyPeriod: 'Per tahun (Hemat 35%)'
        }
    };
    
    // Update Pro plan
    document.getElementById('pro-price').textContent = isYearly ? prices.pro.yearly : prices.pro.monthly;
    document.getElementById('pro-period').textContent = isYearly ? prices.pro.yearlyPeriod : prices.pro.monthlyPeriod;
    
    // Update Elite plan
    document.getElementById('elite-price').textContent = isYearly ? prices.elite.yearly : prices.elite.monthly;
    document.getElementById('elite-period').textContent = isYearly ? prices.elite.yearlyPeriod : prices.elite.monthlyPeriod;
    
    // Update Champion plan
    document.getElementById('champion-price').textContent = isYearly ? prices.champion.yearly : prices.champion.monthly;
    document.getElementById('champion-period').textContent = isYearly ? prices.champion.yearlyPeriod : prices.champion.monthlyPeriod;
});
</script>
@endsection
