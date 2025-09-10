@extends('layouts.app')

@section('title', 'Tim Kami')

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
            <div class="absolute top-1/2 left-1/4 w-2 h-2 bg-white/30 rounded-full"></div>
            <div class="absolute top-1/3 right-1/3 w-1 h-1 bg-white/40 rounded-full"></div>
            <div class="absolute bottom-1/3 left-1/2 w-1.5 h-1.5 bg-white/20 rounded-full"></div>
        </div>

        <div class="relative container mx-auto px-6 py-20">
            <div class="max-w-4xl mx-auto text-center">
                <!-- Main Heading -->
                <h1 class="text-5xl lg:text-6xl font-bold mb-6 tracking-tight">
                    <span class="block text-white">TIM</span>
                    <span class="block text-gray-600 mt-2">PROFESIONAL</span>
                </h1>

                <!-- Subtitle -->
                <p class="text-xl text-gray-400 mb-8 max-w-2xl mx-auto">
                    Berkenalan dengan para ahli yang berdedikasi menciptakan momen-momen tak terlupakan dalam hidup Anda
                </p>

                <!-- Quick Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-12">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-white mb-1">
                            {{ $stats['total_members'] }}</div>
                        <div class="text-sm text-gray-400">Tim Members</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-white mb-1">
                            {{ $stats['active_members'] }}</div>
                        <div class="text-sm text-gray-400">Active Members</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-white mb-1">
                            {{ round($stats['avg_experience'] ?? 0) }}</div>
                        <div class="text-sm text-gray-400">Avg. Experience (Months)</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-white mb-1">
                            {{ number_format($stats['avg_salary'] ?? 0, 0) }}</div>
                        <div class="text-sm text-gray-400">Avg. Salary (K)</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Introduction Section -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="max-w-4xl mx-auto text-center mb-12">
                <h2 class="text-3xl lg:text-4xl font-bold text-black mb-6">Keunggulan Tim Kami</h2>
                <p class="text-lg text-gray-600 leading-relaxed">
                    Setiap anggota tim kami adalah profesional berpengalaman yang berkomitmen memberikan layanan
                    terbaik.
                    Dengan keahlian yang beragam dan semangat kolaborasi, kami siap mewujudkan visi Anda.
                </p>
            </div>

            <!-- Team Strengths Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Expertise -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-lg transition-shadow duration-300">
                    <div class="w-16 h-16 bg-black rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-black mb-4">Expertise Tinggi</h3>
                    <p class="text-gray-600">
                        Tim dengan pengalaman bertahun-tahun di industri wedding organizer dan event management
                        terpercaya.
                    </p>
                </div>

                <!-- Innovation -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-lg transition-shadow duration-300">
                    <div class="w-16 h-16 bg-black rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-black mb-4">Inovasi Terdepan</h3>
                    <p class="text-gray-600">
                        Mengintegrasikan teknologi modern dengan sentuhan personal untuk menciptakan pengalaman yang
                        memukau.
                    </p>
                </div>

                <!-- Dedication -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-lg transition-shadow duration-300">
                    <div class="w-16 h-16 bg-black rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-black mb-4">Dedikasi Penuh</h3>
                    <p class="text-gray-600">
                        Komitmen 100% untuk setiap detail acara, memastikan momen spesial Anda berjalan sempurna.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Members Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-6">
            <!-- Section Header -->
            <div class="text-center mb-12">
                <h2 class="text-3xl lg:text-4xl font-bold text-black mb-4">Bertemu Tim Kami</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Setiap anggota tim membawa keahlian unik dan passion yang sama untuk menciptakan pengalaman luar
                    biasa
                </p>
            </div>

            @if($teamMembers->count() > 0)
                <!-- Team Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                    @foreach($teamMembers as $member)
                        <div
                            class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 hover:border-gray-200">
                            <!-- Member Photo -->
                            <div class="relative overflow-hidden">
                                @if($member->photo)
                                    <img src="{{ asset('storage/' . $member->photo) }}"
                                        alt="{{ $member->name }}"
                                        class="w-full h-64 object-cover group-hover:scale-105 transition-transform duration-300">
                                    <blade
                                        elseif|(%24member-%3EdataPribadi%20%26%26%20%24member-%3EdataPribadi-%3Efoto_url) />
                                    <img src="{{ $member->dataPribadi->foto_url }}" alt="{{ $member->name }}"
                                        class="w-full h-64 object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div
                                        class="w-full h-64 bg-gray-100 flex items-center justify-center group-hover:bg-gray-200 transition-colors duration-300">
                                        <div class="w-20 h-20 bg-black rounded-full flex items-center justify-center">
                                            @php
                                                $nameParts = explode(' ', $member->name);
                                                $initials = strtoupper(substr($nameParts[0], 0, 1));
                                                if (count($nameParts) > 1) {
                                                $initials .= strtoupper(substr(end($nameParts), 0, 1));
                                                }
                                            @endphp
                                            <span class="text-2xl font-bold text-white">{{ $initials }}</span>
                                        </div>
                                    </div>
                                @endif

                                <!-- Overlay with Social Links -->
                                <div
                                    class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-300 flex items-center justify-center">
                                    <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                        @php
                                            $memberData = [
                                            'id' => $member->id,
                                            'name' => $member->name,
                                            'email' => $member->email,
                                            'position' => $member->position,
                                            'photo' => $member->photo,
                                            'salary' => $member->salary,
                                            'date_of_join' => $member->date_of_join,
                                            'date_of_out' => $member->date_of_out,
                                            'em_count' => $member->em_count,
                                            'data_pribadi' => $member->dataPribadi ? [
                                            'foto_url' => $member->dataPribadi->foto_url,
                                            'usia' => $member->dataPribadi->usia,
                                            'tempat_lahir' => $member->dataPribadi->tempat_lahir,
                                            'tanggal_lahir' => $member->dataPribadi->tanggal_lahir,
                                            'nomor_telepon' => $member->dataPribadi->nomor_telepon,
                                            'alamat' => $member->dataPribadi->alamat,
                                            'pendidikan_terakhir' => $member->dataPribadi->pendidikan_terakhir,
                                            'catatan_khusus' => $member->dataPribadi->catatan_khusus,
                                            'motivasi_kerja' => $member->dataPribadi->motivasi_kerja
                                            ] : null
                                            ];
                                        @endphp
                                        <button onclick="showMemberDetail({{ json_encode($memberData) }})"
                                            class="bg-white text-black px-4 py-2 rounded-lg font-medium hover:bg-gray-100 transition-colors">
                                            Lihat Detail
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Member Info -->
                            <div class="p-6">
                                <h3
                                    class="text-xl font-bold text-black mb-2 group-hover:text-gray-600 transition-colors">
                                    {{ $member->name }}
                                </h3>

                                @if($member->position)
                                    <p class="text-gray-600 font-medium mb-3">{{ $member->position }}</p>
                                @endif

                                <!-- Member Stats -->
                                <div class="space-y-2 mb-4">
                                    @if($member->date_of_join)
                                        <div class="flex items-center text-sm text-gray-500">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                            Bergabung
                                            {{ $member->date_of_join->format('M Y') }}
                                        </div>
                                    @endif

                                    @if($member->dataPribadi && $member->dataPribadi->usia)
                                        <div class="flex items-center text-sm text-gray-500">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                                </path>
                                            </svg>
                                            {{ $member->dataPribadi->usia }} tahun
                                        </div>
                                    @endif

                                    @if($member->email)
                                        <div class="flex items-center text-sm text-gray-500">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                            <span class="truncate">{{ $member->email }}</span>
                                        </div>
                                    @endif

                                    @if($member->em_count > 0)
                                        <div class="flex items-center text-sm text-gray-500">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                                </path>
                                            </svg>
                                            {{ $member->em_count }}
                                            Project{{ $member->em_count > 1 ? 's' : '' }}
                                        </div>
                                    @endif
                                </div>

                                <!-- Experience Badge -->
                                @if($member->date_of_join)
                                    @php
                                        $experience = now()->diffInMonths($member->date_of_join);
                                        $experienceYears = floor($experience / 12);
                                        $experienceMonths = $experience % 12;
                                    @endphp
                                    <div class="flex justify-between items-center">
                                        <div
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-600 text-white">
                                            @if($experienceYears > 0)
                                                {{ $experienceYears }} tahun {{ $experienceMonths }} bulan
                                            @else
                                                {{ $experienceMonths }} bulan
                                            @endif
                                        </div>

                                        <!-- Status Badge -->
                                        @if($member->date_of_out)
                                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600">
                                                Alumni
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs rounded-full bg-black text-white">
                                                Active
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-16">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM9 9a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-black mb-2">Tim Sedang Berkembang</h3>
                    <p class="text-gray-600 max-w-md mx-auto">
                        Kami sedang membangun tim yang luar biasa. Stay tuned untuk berkenalan dengan para profesional
                        kami!
                    </p>
                </div>
            @endif
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="bg-black rounded-3xl p-8 md:p-12 text-center">
                <h2 class="text-3xl lg:text-4xl font-bold text-white mb-6">
                    Siap Berkolaborasi dengan Tim Terbaik?
                </h2>
                <p class="text-xl text-gray-300 mb-8 max-w-2xl mx-auto">
                    Mari wujudkan momen istimewa Anda bersama tim profesional yang berpengalaman dan penuh dedikasi
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('kontak') }}"
                        class="bg-white text-black px-8 py-4 rounded-xl font-semibold hover:bg-gray-100 transition-colors duration-300">
                        Hubungi Kami
                    </a>
                    <a href="{{ route('project') }}"
                        class="border-2 border-white text-white px-8 py-4 rounded-xl font-semibold hover:bg-white hover:text-black transition-colors duration-300">
                        Lihat Portfolio
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Member Detail Modal -->
<div id="memberModal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 p-4 hidden">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <!-- Modal Header -->
            <div class="flex justify-between items-start mb-6 pb-4 border-b border-gray-200">
                <div id="modalHeader">
                    <!-- Will be populated by JavaScript -->
                </div>
                <button onclick="closeMemberModal()" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">
                    ×
                </button>
            </div>

            <!-- Modal Content -->
            <div id="modalContent">
                <!-- Will be populated by JavaScript -->
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end mt-6 pt-6 border-t border-gray-200">
                <button onclick="closeMemberModal()"
                    class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-black transition-colors font-medium">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Function to show member detail modal
    function showMemberDetail(member) {
        const modal = document.getElementById('memberModal');
        const modalHeader = document.getElementById('modalHeader');
        const modalContent = document.getElementById('modalContent');

        // Create initials from name
        const nameParts = member.name.split(' ');
        let initials = nameParts[0].charAt(0).toUpperCase();
        if (nameParts.length > 1) {
            initials += nameParts[nameParts.length - 1].charAt(0).toUpperCase();
        }

        // Populate header
        modalHeader.innerHTML = `
                <div class="flex items-center space-x-4">
                    ${member.photo ? 
                        `<img src="/storage/${member.photo}" alt="${member.name}" class="w-16 h-16 rounded-full object-cover">` :
                        member.data_pribadi && member.data_pribadi.foto_url ?
                        `<img src="${member.data_pribadi.foto_url}" alt="${member.name}" class="w-16 h-16 rounded-full object-cover">` :
                        `<div class="w-16 h-16 bg-black rounded-full flex items-center justify-center">
                            <span class="text-xl font-bold text-white">${initials}</span>
                        </div>`
                    }
                    <div>
                        <h3 class="text-2xl font-bold text-black">${member.name}</h3>
                        ${member.position ? `<p class="text-gray-600 font-medium">${member.position}</p>` : ''}
                    </div>
                </div>
            `;

        // Populate content
        modalContent.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Personal Info -->
                    <div class="bg-gray-50 rounded-xl p-4">
                        <h4 class="text-lg font-semibold text-black mb-4">Informasi Personal</h4>
                        <div class="space-y-3">
                            ${member.email ? `
                            <div>
                                <label class="text-sm font-medium text-gray-600">Email:</label>
                                <p class="text-black font-medium">${member.email}</p>
                            </div>
                            ` : ''}
                            ${member.data_pribadi && member.data_pribadi.nomor_telepon ? `
                            <div>
                                <label class="text-sm font-medium text-gray-600">Telepon:</label>
                                <p class="text-black font-medium">${member.data_pribadi.nomor_telepon}</p>
                            </div>
                            ` : ''}
                            ${member.data_pribadi && member.data_pribadi.tanggal_lahir ? `
                            <div>
                                <label class="text-sm font-medium text-gray-600">Tanggal Lahir:</label>
                                <p class="text-black font-medium">${new Date(member.data_pribadi.tanggal_lahir).toLocaleDateString('id-ID')}</p>
                            </div>
                            ` : ''}
                            ${member.data_pribadi && member.data_pribadi.tempat_lahir ? `
                            <div>
                                <label class="text-sm font-medium text-gray-600">Tempat Lahir:</label>
                                <p class="text-black font-medium">${member.data_pribadi.tempat_lahir}</p>
                            </div>
                            ` : ''}
                            ${member.data_pribadi && member.data_pribadi.usia ? `
                            <div>
                                <label class="text-sm font-medium text-gray-600">Usia:</label>
                                <p class="text-black font-medium">${member.data_pribadi.usia} tahun</p>
                            </div>
                            ` : ''}
                            ${member.data_pribadi && member.data_pribadi.alamat ? `
                            <div>
                                <label class="text-sm font-medium text-gray-600">Alamat:</label>
                                <p class="text-black font-medium">${member.data_pribadi.alamat}</p>
                            </div>
                            ` : ''}
                        </div>
                    </div>

                    <!-- Professional Info -->
                    <div class="bg-gray-50 rounded-xl p-4">
                        <h4 class="text-lg font-semibold text-black mb-4">Informasi Profesional</h4>
                        <div class="space-y-3">
                            ${member.date_of_join ? `
                            <div>
                                <label class="text-sm font-medium text-gray-600">Bergabung Sejak:</label>
                                <p class="text-black font-medium">${new Date(member.date_of_join).toLocaleDateString('id-ID')}</p>
                            </div>
                            ` : ''}
                            ${member.salary ? `
                            <div>
                                <label class="text-sm font-medium text-gray-600">Gaji:</label>
                                <p class="text-black font-medium">Rp ${new Intl.NumberFormat('id-ID').format(member.salary)}</p>
                            </div>
                            ` : ''}
                            ${member.em_count > 0 ? `
                            <div>
                                <label class="text-sm font-medium text-gray-600">Total Proyek:</label>
                                <p class="text-black font-medium">${member.em_count} proyek</p>
                            </div>
                            ` : ''}
                            ${member.date_of_out ? `
                            <div>
                                <label class="text-sm font-medium text-gray-600">Status:</label>
                                <p class="text-black font-medium">Alumni (keluar: ${new Date(member.date_of_out).toLocaleDateString('id-ID')})</p>
                            </div>
                            ` : `
                            <div>
                                <label class="text-sm font-medium text-gray-600">Status:</label>
                                <p class="text-black font-medium">Aktif</p>
                            </div>
                            `}
                            ${member.data_pribadi && member.data_pribadi.pendidikan_terakhir ? `
                            <div>
                                <label class="text-sm font-medium text-gray-600">Pendidikan Terakhir:</label>
                                <p class="text-black font-medium">${member.data_pribadi.pendidikan_terakhir}</p>
                            </div>
                            ` : ''}
                        </div>
                    </div>

                    <!-- Additional Info -->
                    ${member.data_pribadi && (member.data_pribadi.catatan_khusus || member.data_pribadi.motivasi_kerja) ? `
                    <div class="md:col-span-2 bg-gray-50 rounded-xl p-4">
                        <h4 class="text-lg font-semibold text-black mb-4">Informasi Tambahan</h4>
                        <div class="space-y-4">
                            ${member.data_pribadi.motivasi_kerja ? `
                            <div>
                                <label class="text-sm font-medium text-gray-600">Motivasi Kerja:</label>
                                <p class="text-black mt-1">${member.data_pribadi.motivasi_kerja}</p>
                            </div>
                            ` : ''}
                            ${member.data_pribadi.catatan_khusus ? `
                            <div>
                                <label class="text-sm font-medium text-gray-600">Catatan Khusus:</label>
                                <p class="text-black mt-1">${member.data_pribadi.catatan_khusus}</p>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                    ` : ''}
                </div>
            `;

        // Show modal
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // Close modal when clicking outside
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                closeMemberModal();
            }
        });
    }

    // Function to close member modal
    function closeMemberModal() {
        const modal = document.getElementById('memberModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Close modal with ESC key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeMemberModal();
        }
    });

    // Smooth scroll for internal links
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

    // Add loading animation for images
    document.addEventListener('DOMContentLoaded', function () {
        const images = document.querySelectorAll('img');
        images.forEach(img => {
            img.addEventListener('load', function () {
                this.classList.add('loaded');
            });
        });
    });

</script>

<style>
    /* Custom styles for smooth animations */
    .card-hover {
        transition: all 0.3s ease;
    }

    .card-hover:hover {
        transform: translateY(-5px);
    }

    img {
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    img.loaded {
        opacity: 1;
    }

    /* Custom scrollbar for modal */
    .overflow-y-auto::-webkit-scrollbar {
        width: 6px;
    }

    .overflow-y-auto::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .overflow-y-auto::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }

    .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

</style>
@endsection
