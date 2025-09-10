@extends('layouts.app')

@section('title', 'Home - Makna Finance')

@section('content')
    <div class="min-h-screen bg-white">
        <!-- Header Navigation -->
        @include('front.header')

        <!-- Hero Section -->
        <section class="relative bg-black text-white overflow-hidden">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-20 left-20 w-32 h-32 border border-white/20 rounded-full"></div>
                <div class="absolute top-40 right-40 w-24 h-24 border border-white/30 rounded-full"></div>
                <div class="absolute bottom-20 left-1/3 w-16 h-16 border border-white/20 rounded-full"></div>
                <div class="absolute bottom-40 right-20 w-20 h-20 border border-white/25 rounded-full"></div>
            </div>
            
            <div class="relative container mx-auto px-6 py-24 lg:py-32">
                <div class="max-w-4xl mx-auto text-center">
                    <!-- Main Heading -->
                    <h1 class="text-5xl lg:text-7xl font-bold mb-6 tracking-tight">
                        <span class="block text-white">MAKNA</span>
                        <span class="block text-gray-600 mt-2">FINANCE</span>
                    </h1>
                    
                    <!-- Subtitle -->
                    <p class="text-xl lg:text-2xl text-gray-600 mb-12 max-w-3xl mx-auto leading-relaxed">
                        Solusi manajemen keuangan terdepan untuk bisnis modern dengan teknologi yang inovatif dan user-friendly
                    </p>
                    
                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                        <a href="#features" class="bg-white text-black px-8 py-4 rounded-lg font-semibold hover:bg-gray-100 transition-all duration-300 transform hover:scale-105 shadow-lg">
                            Jelajahi Fitur
                        </a>
                        <a href="#contact" class="border-2 border-white text-white px-8 py-4 rounded-lg font-semibold hover:bg-white hover:text-black transition-all duration-300">
                            Hubungi Kami
                        </a>
                    </div>
                </div>
                
                <!-- Scroll Indicator -->
                <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2">
                    <div class="animate-bounce">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="py-16 bg-gray-50">
            <div class="container mx-auto px-6">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="text-center">
                        <div class="text-4xl lg:text-5xl font-bold text-black mb-2">500+</div>
                        <div class="text-gray-600 font-medium">Klien Terpercaya</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl lg:text-5xl font-bold text-black mb-2">99.9%</div>
                        <div class="text-gray-600 font-medium">Uptime Sistem</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl lg:text-5xl font-bold text-black mb-2">24/7</div>
                        <div class="text-gray-600 font-medium">Customer Support</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl lg:text-5xl font-bold text-black mb-2">5★</div>
                        <div class="text-gray-600 font-medium">Rating Pengguna</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-20 bg-white">
            <div class="container mx-auto px-6">
                <!-- Section Header -->
                <div class="text-center mb-16">
                    <h2 class="text-4xl lg:text-5xl font-bold text-black mb-6">
                        Fitur Unggulan
                    </h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        Solusi lengkap untuk semua kebutuhan manajemen keuangan bisnis Anda
                    </p>
                </div>

                <!-- Features Grid -->
                <div class="grid lg:grid-cols-3 gap-12">
                    <!-- Feature 1 -->
                    <div class="text-center group">
                        <div class="w-16 h-16 bg-black rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-black mb-4">Analytics Dashboard</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Dashboard analitik real-time yang memberikan insight mendalam tentang performa keuangan bisnis Anda
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="text-center group">
                        <div class="w-16 h-16 bg-black rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-black mb-4">Security First</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Keamanan tingkat enterprise dengan enkripsi end-to-end dan compliance standar internasional
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="text-center group">
                        <div class="w-16 h-16 bg-black rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-black mb-4">Lightning Fast</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Performa super cepat dengan teknologi cloud terdepan untuk pengalaman pengguna yang optimal
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section class="py-20 bg-gray-50">
            <div class="container mx-auto px-6">
                <!-- Section Header -->
                <div class="text-center mb-16">
                    <h2 class="text-4xl lg:text-5xl font-bold text-black mb-6">
                        Layanan Kami
                    </h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        Berbagai layanan yang dirancang khusus untuk mendukung pertumbuhan bisnis Anda
                    </p>
                </div>

                <!-- Services Grid -->
                <div class="grid lg:grid-cols-2 gap-8">
                    <!-- Service 1 -->
                    <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-lg transition-shadow duration-300">
                        <div class="flex items-start space-x-6">
                            <div class="w-12 h-12 bg-black rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-black mb-3">Financial Planning</h3>
                                <p class="text-gray-600 leading-relaxed mb-4">
                                    Perencanaan keuangan komprehensif dengan AI-powered forecasting untuk membantu Anda membuat keputusan bisnis yang tepat.
                                </p>
                                <a href="#" class="text-black font-semibold hover:text-gray-600 transition-colors">
                                    Pelajari lebih lanjut →
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Service 2 -->
                    <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-lg transition-shadow duration-300">
                        <div class="flex items-start space-x-6">
                            <div class="w-12 h-12 bg-black rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-black mb-3">Risk Management</h3>
                                <p class="text-gray-600 leading-relaxed mb-4">
                                    Sistem manajemen risiko terintegrasi untuk mengidentifikasi, menganalisis, dan memitigasi risiko keuangan bisnis.
                                </p>
                                <a href="#" class="text-black font-semibold hover:text-gray-600 transition-colors">
                                    Pelajari lebih lanjut →
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Service 3 -->
                    <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-lg transition-shadow duration-300">
                        <div class="flex items-start space-x-6">
                            <div class="w-12 h-12 bg-black rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-black mb-3">Investment Advisory</h3>
                                <p class="text-gray-600 leading-relaxed mb-4">
                                    Konsultasi investasi profesional dengan algoritma machine learning untuk optimasi portfolio investasi Anda.
                                </p>
                                <a href="#" class="text-black font-semibold hover:text-gray-600 transition-colors">
                                    Pelajari lebih lanjut →
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Service 4 -->
                    <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-lg transition-shadow duration-300">
                        <div class="flex items-start space-x-6">
                            <div class="w-12 h-12 bg-black rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-black mb-3">Compliance & Audit</h3>
                                <p class="text-gray-600 leading-relaxed mb-4">
                                    Sistem compliance otomatis dan audit trail lengkap untuk memastikan bisnis Anda selalu sesuai regulasi.
                                </p>
                                <a href="#" class="text-black font-semibold hover:text-gray-600 transition-colors">
                                    Pelajari lebih lanjut →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonial Section -->
        <section class="py-20 bg-white">
            <div class="container mx-auto px-6">
                <!-- Section Header -->
                <div class="text-center mb-16">
                    <h2 class="text-4xl lg:text-5xl font-bold text-black mb-6">
                        Apa Kata Mereka
                    </h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        Testimoni dari klien-klien yang telah merasakan manfaat platform kami
                    </p>
                </div>

                <!-- Testimonials Grid -->
                <div class="grid lg:grid-cols-3 gap-8">
                    <!-- Testimonial 1 -->
                    <div class="bg-gray-50 rounded-2xl p-8">
                        <div class="mb-6">
                            <div class="flex text-black mb-4">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-600 leading-relaxed">
                                "Platform yang luar biasa! Dashboard yang intuitif dan fitur analytics yang mendalam membantu kami mengoptimalkan cash flow perusahaan."
                            </p>
                        </div>
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-black rounded-full flex items-center justify-center text-white font-bold mr-4">
                                AS
                            </div>
                            <div>
                                <div class="font-bold text-black">Andi Setiawan</div>
                                <div class="text-gray-600 text-sm">CEO, TechStartup</div>
                            </div>
                        </div>
                    </div>

                    <!-- Testimonial 2 -->
                    <div class="bg-gray-50 rounded-2xl p-8">
                        <div class="mb-6">
                            <div class="flex text-black mb-4">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-600 leading-relaxed">
                                "Security yang sangat baik dan support team yang responsif. Kami merasa aman menggunakan platform ini untuk mengelola keuangan perusahaan."
                            </p>
                        </div>
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-black rounded-full flex items-center justify-center text-white font-bold mr-4">
                                DR
                            </div>
                            <div>
                                <div class="font-bold text-black">Diana Rahayu</div>
                                <div class="text-gray-600 text-sm">CFO, RetailCorp</div>
                            </div>
                        </div>
                    </div>

                    <!-- Testimonial 3 -->
                    <div class="bg-gray-50 rounded-2xl p-8">
                        <div class="mb-6">
                            <div class="flex text-black mb-4">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-600 leading-relaxed">
                                "ROI yang sangat bagus! Dalam 6 bulan menggunakan platform ini, efisiensi operasional kami meningkat 40% dan biaya administrasi turun signifikan."
                            </p>
                        </div>
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-black rounded-full flex items-center justify-center text-white font-bold mr-4">
                                BW
                            </div>
                            <div>
                                <div class="font-bold text-black">Budi Wijaya</div>
                                <div class="text-gray-600 text-sm">Director, ManufacturingCo</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 bg-black text-white">
            <div class="container mx-auto px-6 text-center">
                <h2 class="text-4xl lg:text-5xl font-bold mb-6">
                    Siap Untuk Memulai?
                </h2>
                <p class="text-xl text-gray-400 mb-12 max-w-2xl mx-auto">
                    Bergabunglah dengan ribuan perusahaan yang telah mempercayakan manajemen keuangan mereka kepada kami
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="#" class="bg-white text-black px-8 py-4 rounded-lg font-semibold hover:bg-gray-100 transition-all duration-300 transform hover:scale-105">
                        Mulai Uji Coba Gratis
                    </a>
                    <a href="#contact" class="border-2 border-white text-white px-8 py-4 rounded-lg font-semibold hover:bg-white hover:text-black transition-all duration-300">
                        Jadwalkan Demo
                    </a>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer id="contact" class="bg-gray-50 py-16">
            <div class="container mx-auto px-6">
                <div class="grid lg:grid-cols-4 gap-8">
                    <!-- Company Info -->
                    <div>
                        <h3 class="text-2xl font-bold text-black mb-6">MAKNA FINANCE</h3>
                        <p class="text-gray-600 mb-6 leading-relaxed">
                            Solusi manajemen keuangan terdepan untuk bisnis modern dengan teknologi yang inovatif dan user-friendly.
                        </p>
                        <div class="flex space-x-4">
                            <a href="#" class="w-10 h-10 bg-black rounded-full flex items-center justify-center text-white hover:bg-gray-800 transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                                </svg>
                            </a>
                            <a href="#" class="w-10 h-10 bg-black rounded-full flex items-center justify-center text-white hover:bg-gray-800 transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"/>
                                </svg>
                            </a>
                            <a href="#" class="w-10 h-10 bg-black rounded-full flex items-center justify-center text-white hover:bg-gray-800 transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Services -->
                    <div>
                        <h4 class="text-lg font-bold text-black mb-6">Layanan</h4>
                        <ul class="space-y-3">
                            <li><a href="#" class="text-gray-600 hover:text-black transition-colors">Financial Planning</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-black transition-colors">Risk Management</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-black transition-colors">Investment Advisory</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-black transition-colors">Compliance & Audit</a></li>
                        </ul>
                    </div>

                    <!-- Company -->
                    <div>
                        <h4 class="text-lg font-bold text-black mb-6">Perusahaan</h4>
                        <ul class="space-y-3">
                            <li><a href="#" class="text-gray-600 hover:text-black transition-colors">Tentang Kami</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-black transition-colors">Karir</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-black transition-colors">Blog</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-black transition-colors">Press</a></li>
                        </ul>
                    </div>

                    <!-- Support -->
                    <div>
                        <h4 class="text-lg font-bold text-black mb-6">Support</h4>
                        <ul class="space-y-3">
                            <li><a href="#" class="text-gray-600 hover:text-black transition-colors">Help Center</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-black transition-colors">Documentation</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-black transition-colors">API Reference</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-black transition-colors">Contact Support</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Footer Bottom -->
                <div class="border-t border-gray-200 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
                    <div class="text-gray-600 text-sm">
                        © 2025 Makna Finance. All rights reserved.
                    </div>
                    <div class="flex space-x-6 mt-4 md:mt-0">
                        <a href="#" class="text-gray-600 hover:text-black text-sm transition-colors">Privacy Policy</a>
                        <a href="#" class="text-gray-600 hover:text-black text-sm transition-colors">Terms of Service</a>
                        <a href="#" class="text-gray-600 hover:text-black text-sm transition-colors">Cookie Policy</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- JavaScript for smooth scrolling -->
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add scroll effect to hero section
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const hero = document.querySelector('section');
            if (hero) {
                hero.style.transform = `translateY(${scrolled * 0.5}px)`;
            }
        });
    </script>
@endsection
