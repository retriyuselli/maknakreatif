@extends('layouts.app')

@section('title', 'Portal Internal - Wedding Organizer')

@section('content')
    <!-- Include Header -->
    @include('front.header')

    <!-- Hero Section -->
    <section class="relative h-screen flex items-center justify-center overflow-hidden">
        <!-- Background Image from Unsplash -->
        <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1755956726885-8c0cbf65c4ae?q=80&w=3540&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2071&q=80"
                alt="Professional Teamwork" class="w-full h-full object-cover">
            <!-- Dark Overlay -->
            <div
                class="absolute inset-0 bg-gradient-to-r from-primary-black/70 via-primary-blue/50 to-primary-black/70">
            </div>
        </div>

        <!-- Hero Content -->
        <div class="relative z-10 text-center px-4 max-w-4xl mx-auto">
            <h1 class="text-4xl md:text-6xl font-bold text-white mb-6 leading-tight">
                <span class="block mb-2">Tetap Positif,</span>
                <span class="block mb-2 text-primary-yellow">Tetap Berkarya,</span>
                <span class="block">dan Jadilah Versi Terbaik Dirimu</span>
            </h1>
            <p class="text-xl md:text-2xl text-gray-200 mb-8 font-light">
                Portal Internal Wedding Organizer Professional
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button
                    class="bg-primary-yellow hover:bg-accent-yellow text-primary-black font-semibold px-8 py-4 rounded-lg transition duration-300 transform hover:scale-105 shadow-lg">
                    Mulai Hari Ini
                </button>
                <button
                    class="border-2 border-white text-white hover:bg-white hover:text-primary-black font-semibold px-8 py-4 rounded-lg transition duration-300">
                    Lihat Dashboard
                </button>
            </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m0 0l7-7">
                </path>
            </svg>
        </div>
    </section>

    <!-- Team Image Slider / Carousel -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-primary-black mb-4">
                    Tim <span class="text-primary-blue">Profesional</span> Kami
                </h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Bersama-sama menciptakan momen indah untuk setiap pasangan
                </p>
            </div>

            <!-- Carousel Container -->
            <div class="relative" x-data="{
                currentSlide: 0,
                slides: [{
                        image: 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                        title: 'Tim Kreatif',
                        description: 'Menghadirkan ide-ide segar untuk setiap event'
                    },
                    {
                        image: 'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                        title: 'Tim Koordinator',
                        description: 'Memastikan setiap detail berjalan sempurna'
                    },
                    {
                        image: 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                        title: 'Tim Eksekusi',
                        description: 'Mewujudkan mimpi menjadi kenyataan'
                    },
                    {
                        image: 'https://images.unsplash.com/photo-1552664730-d307ca884978?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                        title: 'Tim Support',
                        description: 'Memberikan dukungan penuh di setiap langkah'
                    }
                ],
                autoSlide() {
                    setInterval(() => {
                        this.currentSlide = (this.currentSlide + 1) % this.slides.length;
                    }, 4000);
                }
            }" x-init="autoSlide()">

                <!-- Slides -->
                <div class="overflow-hidden rounded-2xl shadow-2xl">
                    <div class="flex transition-transform duration-500 ease-in-out"
                        :style="`transform: translateX(-${currentSlide * 100}%)`">
                        <template x-for="(slide, index) in slides" :key="index">
                            <div class="w-full flex-shrink-0 relative">
                                <div class="grid md:grid-cols-2 gap-8 items-center bg-white p-8 md:p-12">
                                    <div class="order-2 md:order-1">
                                        <h3 class="text-2xl md:text-3xl font-bold text-primary-black mb-4"
                                            x-text="slide.title"></h3>
                                        <p class="text-lg text-gray-600 mb-6" x-text="slide.description"></p>
                                        <div class="flex items-center space-x-4">
                                            <div class="w-12 h-1 bg-primary-yellow rounded"></div>
                                            <span class="text-primary-blue font-semibold">Professional Team</span>
                                        </div>
                                    </div>
                                    <div class="order-1 md:order-2">
                                        <img :src="slide.image" :alt="slide.title"
                                            class="w-full h-64 md:h-80 object-cover rounded-xl shadow-lg">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Navigation Dots -->
                <div class="flex justify-center mt-8 space-x-2">
                    <template x-for="(slide, index) in slides" :key="index">
                        <button @click="currentSlide = index"
                            :class="currentSlide === index ? 'bg-primary-blue' : 'bg-gray-300'"
                            class="w-3 h-3 rounded-full transition-colors duration-300"></button>
                    </template>
                </div>

                <!-- Navigation Arrows -->
                <button @click="currentSlide = currentSlide === 0 ? slides.length - 1 : currentSlide - 1"
                    class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white/80 hover:bg-white text-primary-black p-3 rounded-full shadow-lg transition duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                        </path>
                    </svg>
                </button>
                <button @click="currentSlide = (currentSlide + 1) % slides.length"
                    class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white/80 hover:bg-white text-primary-black p-3 rounded-full shadow-lg transition duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>
    </section>

    <!-- Motivational Quote Section -->
    <section class="py-20 bg-gradient-to-r from-primary-black via-primary-blue to-primary-black">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="relative">
                <!-- Quote Icon -->
                <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                    <svg class="w-12 h-12 text-primary-yellow opacity-50" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
                    </svg>
                </div>

                <!-- Quote Text -->
                <blockquote class="text-2xl md:text-4xl font-light text-white leading-relaxed mb-8 pt-8">
                    "Berpikir positif setiap hari adalah awal dari
                    <span class="text-primary-yellow font-semibold">pelayanan terbaik</span>.
                    Mari terus tumbuh dan berkembang bersama."
                </blockquote>

                <!-- Decorative Elements -->
                <div class="flex justify-center items-center space-x-4 mb-6">
                    <div class="w-16 h-1 bg-primary-yellow rounded"></div>
                    <div class="w-3 h-3 bg-primary-yellow rounded-full"></div>
                    <div class="w-16 h-1 bg-primary-yellow rounded"></div>
                </div>

                <p class="text-lg text-gray-300 font-medium">
                    — Tim Wedding Organizer Professional —
                </p>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="py-16 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Card 1 -->
                <div
                    class="text-center p-8 rounded-xl bg-gradient-to-br from-primary-blue to-accent-blue text-white shadow-xl transform hover:scale-105 transition duration-300">
                    <div class="w-16 h-16 bg-primary-yellow rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-primary-black" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-4">Kualitas Terjamin</h3>
                    <p class="text-blue-100">Setiap project dikerjakan dengan standar kualitas tertinggi dan perhatian
                        detail yang maksimal.</p>
                </div>

                <!-- Card 2 -->
                <div
                    class="text-center p-8 rounded-xl bg-gradient-to-br from-primary-yellow to-accent-yellow text-primary-black shadow-xl transform hover:scale-105 transition duration-300">
                    <div class="w-16 h-16 bg-primary-black rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-4">Tim Solid</h3>
                    <p class="text-yellow-800">Kerjasama tim yang kompak dan profesional untuk menghadirkan hasil
                        terbaik di setiap event.</p>
                </div>

                <!-- Card 3 -->
                <div
                    class="text-center p-8 rounded-xl bg-gradient-to-br from-gray-800 to-primary-black text-white shadow-xl transform hover:scale-105 transition duration-300">
                    <div
                        class="w-16 h-16 bg-primary-yellow rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-primary-black" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-4">Inovasi Berkelanjutan</h3>
                    <p class="text-gray-300">Selalu menghadirkan ide-ide fresh dan solusi kreatif untuk setiap
                        tantangan yang dihadapi.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-primary-black text-white py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h3 class="text-2xl font-bold mb-4">Wedding Organizer Professional</h3>
                <p class="text-gray-400 mb-6">Portal Internal - Menciptakan Momen Indah Bersama</p>
                <div class="flex justify-center space-x-6">
                    <a href="#" class="text-gray-400 hover:text-primary-yellow transition duration-300">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z" />
                        </svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-primary-yellow transition duration-300">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z" />
                        </svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-primary-yellow transition duration-300">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.746-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24.009c6.624 0 11.99-5.367 11.99-11.988C24.007 5.367 18.641.001.012.001z" />
                        </svg>
                    </a>
                </div>
                <div class="mt-8 pt-8 border-t border-gray-800 text-sm text-gray-500">
                    <p>&copy; 2024 Wedding Organizer Professional. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>
@endsection
