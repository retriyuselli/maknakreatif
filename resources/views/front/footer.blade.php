<footer class="bg-white text-gray-800 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div class="md:col-span-1">
                    <div class="flex items-center space-x-3 mb-4">
                        <img src="{{ asset('images/maknawofins_logo.png') }}" alt="Wofins" class="h-12 w-auto">
                    </div>
                    <p class="text-gray-800 text-sm leading-relaxed">
                        Platform terdepan untuk manajemen operasional wedding organizer di Indonesia.
                    </p>
                </div>

                <!-- Product -->
                <div>
                    <h3 class="font-semibold mb-4">Produk</h3>
                    <ul class="space-y-2 text-sm text-gray-800">
                        <li><a href="#features" class="hover:text-white transition-colors">Fitur</a></li>
                        <li><a href="{{ route('harga') }}" class="hover:text-white transition-colors">Harga</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Integrasi</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">API</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h3 class="font-semibold mb-4">Dukungan</h3>
                    <ul class="space-y-2 text-sm text-gray-800">
                        <li><a href="#" class="hover:text-white transition-colors">Bantuan</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Dokumentasi</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Kontak</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Status System</a></li>
                    </ul>
                </div>

                <!-- Company -->
                <div>
                    <h3 class="font-semibold mb-4">Perusahaan</h3>
                    <ul class="space-y-2 text-sm text-gray-800">
                        <li><a href="#" class="hover:text-white transition-colors">Tentang Kami</a></li>
                        <li><a href="{{ route('blog') }}" class="hover:text-white transition-colors">Blog</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Karir</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Privacy</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-5 pt-2 border-t border-gray-800 flex flex-col sm:flex-row justify-between items-center">
                <p class="text-sm text-gray-800">
                    © {{ now()->year }} PT. Makna Kreatif Indonesia. All rights reserved.
                </p>
                {{-- <div class="flex space-x-6 mt-4 sm:mt-0">
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <span class="sr-only">Twitter</span>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M6.29 18.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0020 3.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.073 4.073 0 01.8 7.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 010 16.407a11.616 11.616 0 006.29 1.84"/>
                        </svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <span class="sr-only">LinkedIn</span>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.338 16.338H13.67V12.16c0-.995-.017-2.277-1.387-2.277-1.39 0-1.601 1.086-1.601 2.207v4.248H8.014v-8.59h2.559v1.174h.037c.356-.675 1.227-1.387 2.526-1.387 2.703 0 3.203 1.778 3.203 4.092v4.711zM5.005 6.575a1.548 1.548 0 11-.003-3.096 1.548 1.548 0 01.003 3.096zm-1.337 9.763H6.34v-8.59H3.667v8.59zM17.668 1H2.328C1.595 1 1 1.581 1 2.298v15.403C1 18.418 1.595 19 2.328 19h15.34c.734 0 1.332-.582 1.332-1.299V2.298C19 1.581 18.402 1 17.668 1z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <span class="sr-only">Instagram</span>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2C7.791 2 7.516 2.01 6.596 2.052 5.68 2.094 5.035 2.226 4.447 2.42a5.926 5.926 0 00-2.027 1.34A5.926 5.926 0 001.08 5.787c-.194.588-.326 1.233-.368 2.149C.67 8.856.66 9.131.66 11.34s.01 2.484.052 3.404c.042.916.174 1.561.368 2.149a5.926 5.926 0 001.34 2.027 5.926 5.926 0 002.027 1.34c.588.194 1.233.326 2.149.368.92.042 1.195.052 3.404.052s2.484-.01 3.404-.052c.916-.042 1.561-.174 2.149-.368a5.926 5.926 0 002.027-1.34 5.926 5.926 0 001.34-2.027c.194-.588.326-1.233.368-2.149.042-.92.052-1.195.052-3.404s-.01-2.484-.052-3.404c-.042-.916-.174-1.561-.368-2.149a5.926 5.926 0 00-1.34-2.027A5.926 5.926 0 0014.213.42C13.625.226 12.98.094 12.064.052 11.144.01 10.869 0 8.66 0 6.451 0 6.176.01 5.256.052 4.34.094 3.695.226 3.107.42a5.926 5.926 0 00-2.027 1.34A5.926 5.926 0 00.42 3.787C.226 4.375.094 5.02.052 5.936.01 6.856 0 7.131 0 9.34s.01 2.484.052 3.404c.042.916.174 1.561.368 2.149a5.926 5.926 0 001.34 2.027 5.926 5.926 0 002.027 1.34c.588.194 1.233.326 2.149.368.92.042 1.195.052 3.404.052s2.484-.01 3.404-.052c.916-.042 1.561-.174 2.149-.368a5.926 5.926 0 002.027-1.34 5.926 5.926 0 001.34-2.027c.194-.588.326-1.233.368-2.149.042-.92.052-1.195.052-3.404s-.01-2.484-.052-3.404c-.042-.916-.174-1.561-.368-2.149a5.926 5.926 0 00-1.34-2.027A5.926 5.926 0 0015.787.42C15.199.226 14.554.094 13.638.052 12.718.01 12.443 0 10.234 0 8.025 0 7.75.01 6.83.052 5.914.094 5.269.226 4.681.42a5.926 5.926 0 00-2.027 1.34A5.926 5.926 0 00.42 3.787C.226 4.375.094 5.02.052 5.936.01 6.856 0 7.131 0 9.34s.01 2.484.052 3.404c.042.916.174 1.561.368 2.149a5.926 5.926 0 001.34 2.027 5.926 5.926 0 002.027 1.34c.588.194 1.233.326 2.149.368.92.042 1.195.052 3.404.052z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                </div> --}}
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp Button -->
    <div class="fixed bottom-6 right-6 z-50">
        <a href="#" 
           id="whatsapp-btn"
           onclick="openWhatsApp()"
           class="group bg-green-500 hover:bg-green-600 text-white p-4 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-110 flex items-center justify-center">
            <!-- WhatsApp Icon -->
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.087"/>
            </svg>
            
            <!-- Pulse animation -->
            <div class="absolute inset-0 rounded-full bg-green-400 opacity-30 animate-ping"></div>
        </a>
        
        <!-- Tooltip -->
        <div class="absolute bottom-full right-0 mb-2 bg-gray-800 text-gray-300 text-sm px-3 py-2 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
            Chat dengan kami di WhatsApp
            <div class="absolute top-full right-4 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-800"></div>
        </div>
    </div>

    <script>
        function generateRequestId() {
            // Get current date
            const now = new Date();
            const day = String(now.getDate()).padStart(2, '0');
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const year = now.getFullYear().toString().slice(-2);
            
            // Format date as DDMMYY
            const dateString = `${day}${month}${year}`;
            
            // Get or increment counter from localStorage
            let counter = localStorage.getItem('wofins_request_counter');
            if (!counter) {
                counter = 1;
            } else {
                counter = parseInt(counter) + 1;
            }
            localStorage.setItem('wofins_request_counter', counter);
            
            // Format counter to 3 digits
            const formattedCounter = String(counter).padStart(3, '0');
            
            // Create request ID: [Req.T + DDMMYY + - + XXX]
            const requestId = `[Req.T${dateString}-${formattedCounter}]`;
            
            return requestId;
        }

        function openWhatsApp() {
            const requestId = generateRequestId();
            const message = `${requestId} - Halo, Saya tertarik dengan fitur WOFINS untuk sistem wedding organizer dan ingin mengetahui lebih lanjut tentang demo, pricing, dan implementasinya. Terima kasih!`;
            
            const phoneNumber = '6281373183794';
            const encodedMessage = encodeURIComponent(message);
            const whatsappUrl = `https://wa.me/${phoneNumber}?text=${encodedMessage}`;
            
            window.open(whatsappUrl, '_blank');
        }
    </script>