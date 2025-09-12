@extends('front.layouts.app')

@section('title', 'Harga Paket - WOFINS')

@section('content')
<!-- Header -->
<section class="bg-gradient-to-br from-blue-600 to-purple-700 text-white py-20">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-6">Paket Harga Terjangkau</h1>
        <p class="text-xl text-blue-100 mb-8 max-w-3xl mx-auto">
            Pilih paket yang sesuai dengan kebutuhan bisnis wedding organizer Anda. 
            Semua paket dirancang khusus untuk membantu Anda mengelola acara pernikahan dengan mudah dan profesional.
        </p>
    </div>
</section>

<!-- Pricing Cards -->
<section class="py-20 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Pilih Paket yang Tepat untuk Anda</h2>
            <p class="text-gray-600 text-lg">Dapatkan semua fitur yang Anda butuhkan untuk mengelola bisnis wedding organizer</p>
        </div>
        
        <!-- 2-Column Pricing Cards -->
        <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
            <!-- Non Hastana Plan -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8 hover:shadow-xl transition-all duration-300">
                <div class="text-center mb-8">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Non Hastana</h3>
                    <div class="mb-4">
                        <span class="text-4xl font-bold text-blue-600">Rp 299.000</span>
                        <span class="text-gray-500 text-lg">/event</span>
                    </div>
                    <p class="text-gray-500">Untuk wedding organizer pemula</p>
                </div>
                
                <button class="w-full py-3 px-6 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors mb-8">
                    Mulai Sekarang
                </button>

                <div class="space-y-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-700">Manajemen Event Dasar</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-700">Database Vendor Standar</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-700">Timeline Pernikahan</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-700">Budget Tracker</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-700">Email Support</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-700">Template Kontrak Standar</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-700">Laporan Event Sederhana</span>
                    </div>
                </div>
            </div>

            <!-- Anggota Hastana Plan -->
            <div class="bg-white rounded-2xl shadow-lg border-2 border-purple-500 p-8 hover:shadow-xl transition-all duration-300 relative">
                <div class="absolute -top-3 left-1/2 transform -translate-x-1/2">
                    <span class="bg-purple-500 text-white px-4 py-1 rounded-full text-sm font-bold">Anggota Hastana</span>
                </div>
                <div class="absolute -top-3 right-4">
                    <span class="bg-yellow-400 text-black px-3 py-1 rounded-full text-xs font-bold">Popular</span>
                </div>
                
                <div class="text-center mb-8">
                    <h3 class="text-2xl font-bold text-purple-600 mb-2">Anggota Hastana</h3>
                    <div class="mb-4">
                        <span class="text-4xl font-bold text-purple-600">Rp 249.000</span>
                        <span class="text-gray-500 text-lg">/event</span>
                    </div>
                    <p class="text-gray-500">Untuk member komunitas Hastana</p>
                </div>
                
                <button class="w-full py-3 px-6 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 transition-colors mb-8">
                    Bergabung Sekarang
                </button>

                <div class="text-center mb-6">
                    <p class="text-purple-600 font-semibold">Semua fitur Non Hastana +</p>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-700">Database Vendor Premium</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-700">Priority Support 24/7</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-700">Advanced Analytics</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-700">Custom Branding</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-700">Networking Hastana Community</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-700">Exclusive Training & Workshops</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-700">Referral Program Benefits</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
