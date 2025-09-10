@extends('layouts.app')

@section('title', 'Manajemen Project')

@section('content')
    <div class="min-h-screen bg-white">
        <!-- Header Navigation -->
        @include('front.header')

        <!-- Hero Section -->
                <!-- Hero Section -->
        <section class="relative bg-blue-600 text-white overflow-hidden">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-20 left-20 w-32 h-32 border border-white/20 rounded-full"></div>
                <div class="absolute top-40 right-40 w-24 h-24 border border-white/30 rounded-full"></div>
                <div class="absolute bottom-20 left-1/3 w-16 h-16 border border-white/20 rounded-full"></div>
                <div class="absolute bottom-40 right-20 w-20 h-20 border border-white/25 rounded-full"></div>
            </div>
            
            <div class="relative container mx-auto px-6 py-20">
                <div class="max-w-4xl mx-auto text-center">
                    <!-- Main Heading -->
                    <h1 class="text-5xl lg:text-6xl font-bold mb-6 tracking-tight">
                        <span class="block text-white">PROJECT</span>
                        <span class="block text-blue-200 mt-2">MANAGEMENT</span>
                    </h1>
                    
                    <!-- Subtitle -->
                    <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
                        Kelola semua project dengan sistem terintegrasi untuk monitoring dan tracking yang optimal
                    </p>
                    
                    <!-- Quick Actions -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                        <a href="/admin/orders/create" class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-blue-50 transition-all duration-300 transform hover:scale-105 shadow-lg">
                            ➕ Tambah Project
                        </a>
                        <button onclick="exportProjects()" class="border-2 border-white text-white px-6 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition-all duration-300">
                            📊 Export Data
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Statistics Section -->
        <section class="py-16 bg-gray-50">
            <div class="container mx-auto px-6">
                <!-- Section Header -->
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-blue-600 mb-4">Statistik Project</h2>
                    <p class="text-gray-600">Dashboard overview untuk monitoring performa project</p>
                </div>

                <!-- Main Stats Grid -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Projects -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition-shadow duration-300">
                        <div class="text-center">
                            <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <div class="text-2xl font-bold text-blue-600 mb-1">{{ $stats['total_projects'] ?? 0 }}</div>
                            <div class="text-sm text-gray-600">Total Project</div>
                        </div>
                    </div>

                    <!-- Active Projects -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition-shadow duration-300">
                        <div class="text-center">
                            <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div class="text-2xl font-bold text-blue-600 mb-1">{{ $stats['active_projects'] ?? 0 }}</div>
                            <div class="text-sm text-gray-600">Project Aktif</div>
                        </div>
                    </div>

                    <!-- Completed Projects -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition-shadow duration-300">
                        <div class="text-center">
                            <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="text-2xl font-bold text-blue-600 mb-1">{{ $stats['completed_projects'] ?? 0 }}</div>
                            <div class="text-sm text-gray-600">Project Selesai</div>
                        </div>
                    </div>

                    <!-- Revenue -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition-shadow duration-300">
                        <div class="text-center">
                            <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <div class="text-2xl font-bold text-blue-600 mb-1">Rp {{ number_format($stats['total_revenue_this_year'] ?? 0, 0, ',', '.') }}</div>
                            <div class="text-sm text-gray-600">Revenue 2025</div>
                        </div>
                    </div>
                </div>

                <!-- Additional Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Payment Stats -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition-shadow duration-300">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-blue-600">Status Pembayaran</h3>
                            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Lunas</span>
                                <span class="font-semibold text-blue-600">{{ $stats['paid_projects'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Belum Lunas</span>
                                <span class="font-semibold text-blue-600">{{ $stats['unpaid_projects'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Average Value -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition-shadow duration-300">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-blue-600">Rata-rata Nilai</h3>
                            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="text-2xl font-bold text-blue-600">
                            Rp {{ number_format($stats['average_project_value'] ?? 0, 0, ',', '.') }}
                        </div>
                        <div class="text-sm text-gray-600">Per project</div>
                    </div>

                    <!-- Monthly Stats -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition-shadow duration-300">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-blue-600">Bulan Ini</h3>
                            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="text-2xl font-bold text-blue-600">{{ $stats['projects_this_month'] ?? 0 }}</div>
                        <div class="text-sm text-gray-600">Project baru</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Filter & Search Section -->
        <section class="py-8 bg-white">
            <div class="container mx-auto px-6">
                <div class="bg-gray-50 rounded-2xl p-6 shadow-sm">
                    <form method="GET" action="{{ route('project') }}" class="space-y-4">
                        <!-- Search and Quick Filters Row -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Search Bar -->
                            <div class="relative md:col-span-2">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <input type="text" name="search" value="{{ request('search') }}" 
                                    placeholder="Cari project, nomor kontrak, atau klien..."
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent bg-white">
                            </div>

                            <!-- Quick Action -->
                            <div class="flex gap-2">
                                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-lg transition-colors font-medium">
                                    🔍 Cari
                                </button>
                                <a href="{{ route('project') }}" class="bg-blue-500 hover:bg-blue-600 text-white py-3 px-4 rounded-lg transition-colors font-medium">
                                    🔄
                                </a>
                            </div>
                        </div>

                        <!-- Advanced Filters Row -->
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <!-- Status Filter -->
                            <div class="relative">
                                <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent appearance-none bg-white">
                                    <option value="">Semua Status</option>
                                    @if(isset($statusOptions))
                                        @foreach($statusOptions as $value => $label)
                                            <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>

                            <!-- Client Filter -->
                            <div class="relative">
                                <select name="user_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent appearance-none bg-white">
                                    <option value="">Semua Klien</option>
                                    @if(isset($users))
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>

                            <!-- Employee Filter -->
                            <div class="relative">
                                <select name="employee_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent appearance-none bg-white">
                                    <option value="">Semua Employee</option>
                                    @if(isset($employees))
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                                {{ $employee->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>

                            <!-- Payment Status Filter -->
                            <div class="relative">
                                <select name="is_paid" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent appearance-none bg-white">
                                    <option value="">Semua Pembayaran</option>
                                    <option value="1" {{ request('is_paid') == '1' ? 'selected' : '' }}>Lunas</option>
                                    <option value="0" {{ request('is_paid') == '0' ? 'selected' : '' }}>Belum Lunas</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>

                            <!-- Date Range -->
                            <div class="relative">
                                <input type="date" name="start_date" value="{{ request('start_date') }}" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent bg-white">
                            </div>
                        </div>
                    </form>

                    <!-- Filter Summary -->
                    @if(request()->hasAny(['search', 'status', 'user_id', 'employee_id', 'is_paid', 'start_date']))
                        <div class="mt-4 p-4 bg-white rounded-lg border border-gray-200">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <div class="text-sm text-gray-600">
                                    <strong class="text-blue-600">Filter aktif:</strong>
                                    @if(request('search'))
                                        <span class="ml-2 px-3 py-1 bg-blue-600 text-white rounded-full text-xs">Pencarian: "{{ request('search') }}"</span>
                                    @endif
                                    @if(request('status') && isset($statusOptions))
                                        <span class="ml-2 px-3 py-1 bg-blue-600 text-white rounded-full text-xs">Status: {{ $statusOptions[request('status')] ?? request('status') }}</span>
                                    @endif
                                </div>
                                <a href="{{ route('project') }}" class="text-gray-600 hover:text-blue-600 text-sm font-medium">
                                    Hapus semua filter
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <!-- Projects Grid Section -->
        <section class="py-8 bg-gray-50">
            <div class="container mx-auto px-6">
                <!-- Header with pagination info -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-blue-600">Daftar Project</h2>
                        @if(isset($projects) && method_exists($projects, 'total'))
                            <p class="text-gray-600">Menampilkan {{ $projects->firstItem() }} - {{ $projects->lastItem() }} dari {{ $projects->total() }} project</p>
                        @endif
                    </div>
                    <div class="flex items-center space-x-3">
                        <button onclick="exportProjects()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm transition-colors font-medium">
                            📊 Export
                        </button>
                        <a href="/admin/orders/create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            ➕ Tambah Project
                        </a>
                    </div>
                </div>

                <!-- Projects Grid -->
                @if(isset($projects) && $projects->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($projects as $project)
                        <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-shadow duration-300 overflow-hidden">
                            <!-- Project Header -->
                            <div class="bg-blue-600 text-white p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-bold text-lg truncate">{{ $project->name }}</h3>
                                        <p class="text-blue-100 text-sm">{{ $project->number }}</p>
                                    </div>
                                    <!-- Status Badge -->
                                    @if($project->status)
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-white text-blue-600">
                                            {{ $project->status->getLabel() }}
                                        </span>
                                    @else
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-500 text-white">
                                            N/A
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Project Content -->
                            <div class="p-4 space-y-4">
                                <!-- Client Info -->
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-blue-600">{{ $project->user->name ?? 'Client tidak tersedia' }}</p>
                                        <p class="text-sm text-gray-600">Klien</p>
                                    </div>
                                </div>

                                <!-- Employee Info -->
                                @if($project->employee)
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H8a2 2 0 01-2-2V8a2 2 0 012-2V6"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-blue-600">{{ $project->employee->name }}</p>
                                        <p class="text-sm text-gray-600">Account Manager</p>
                                    </div>
                                </div>
                                @endif

                                <!-- Project Details -->
                                <div class="grid grid-cols-2 gap-4">
                                    @if($project->no_kontrak)
                                    <div>
                                        <p class="text-xs text-gray-600 uppercase tracking-wide">Kontrak</p>
                                        <p class="font-medium text-blue-600">{{ $project->no_kontrak }}</p>
                                    </div>
                                    @endif

                                    @if($project->pax)
                                    <div>
                                        <p class="text-xs text-gray-600 uppercase tracking-wide">Pax</p>
                                        <p class="font-medium text-blue-600">{{ $project->pax }} orang</p>
                                    </div>
                                    @endif
                                </div>

                                <!-- Financial Info -->
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm text-gray-600">Total Nilai</span>
                                        <span class="font-bold text-blue-600">Rp {{ number_format($project->total_price, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Terbayar</span>
                                        <span class="font-medium text-blue-600">Rp {{ number_format($project->paid_amount, 0, ',', '.') }}</span>
                                    </div>
                                    <!-- Payment Status -->
                                    <div class="mt-2 flex justify-end">
                                        @if($project->is_paid)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-600 text-white">
                                                ✓ Lunas
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-500 text-white">
                                                ⏳ Belum Lunas
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Created Date -->
                                <div class="text-center">
                                    <p class="text-xs text-gray-500">Dibuat: {{ $project->created_at->format('d M Y') }}</p>
                                </div>
                            </div>

                            <!-- Project Actions -->
                            <div class="p-4 bg-gray-50 border-t border-gray-200">
                                <div class="flex space-x-2">
                                    <button onclick="viewProjectDetail({{ $project->id }})" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-2 px-3 rounded-lg text-sm font-medium transition-colors">
                                        👁️ Lihat
                                    </button>
                                    <a href="/admin/orders/{{ $project->id }}/edit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 px-3 rounded-lg text-sm font-medium transition-colors text-center">
                                        ✏️ Edit
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="bg-white rounded-2xl shadow-sm p-12 text-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-blue-600 mb-2">Belum ada project</h3>
                        <p class="text-gray-600 mb-6">Silakan tambahkan project pertama Anda</p>
                        <a href="/admin/orders/create" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                            ➕ Tambah Project Sekarang
                        </a>
                    </div>
                @endif

                <!-- Pagination -->
                @if(isset($projects) && method_exists($projects, 'hasPages') && $projects->hasPages())
                    <div class="mt-8 bg-white rounded-2xl shadow-sm p-6">
                        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                            <div class="text-sm text-gray-600">
                                Menampilkan {{ $projects->firstItem() }} sampai {{ $projects->lastItem() }} dari {{ $projects->total() }} project
                            </div>
                            <div class="flex items-center">
                                {{ $projects->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </div>

    <!-- JavaScript Functions -->
    <script>
        // Function untuk export projects
        function exportProjects() {
            @if(Route::has('project.export'))
                const url = new URL('{{ route("project.export") }}', window.location.origin);
            @else
                const url = new URL('/project-export', window.location.origin);
            @endif
            
            const params = new URLSearchParams(window.location.search);
            params.forEach((value, key) => {
                url.searchParams.append(key, value);
            });
            
            window.location.href = url.toString();
        }

        // Function untuk melihat detail project
        function viewProjectDetail(projectId) {
            const projects = @json($projects->items() ?? []);
            const selectedProject = projects.find(p => p.id === projectId);
            
            if (selectedProject) {
                showProjectModal(selectedProject);
            } else {
                window.location.href = `/admin/orders/${projectId}`;
            }
        }

        // Function untuk menampilkan modal detail project
        function showProjectModal(project) {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';
            modal.innerHTML = `
                <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                    <div class="p-6">
                        <!-- Modal Header -->
                        <div class="flex justify-between items-start mb-6 pb-4 border-b border-gray-200">
                            <div>
                                <h3 class="text-2xl font-bold text-black">${project.name}</h3>
                                <p class="text-gray-600">${project.number}</p>
                            </div>
                            <button onclick="closeProjectModal()" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">
                                ×
                            </button>
                        </div>

                        <!-- Modal Content -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Project Info -->
                            <div class="bg-gray-50 rounded-xl p-4">
                                <h4 class="text-lg font-semibold text-black mb-4">Informasi Project</h4>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-sm font-medium text-gray-600">Status:</label>
                                        <span class="ml-2 px-2 py-1 rounded-full text-xs font-medium bg-gray-600 text-white">
                                            ${project.status?.label || project.status || 'N/A'}
                                        </span>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-600">Klien:</label>
                                        <p class="text-black font-medium">${project.user?.name || 'Tidak tersedia'}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-600">Account Manager:</label>
                                        <p class="text-black font-medium">${project.employee?.name || 'Tidak tersedia'}</p>
                                    </div>
                                    ${project.no_kontrak ? `
                                    <div>
                                        <label class="text-sm font-medium text-gray-600">No. Kontrak:</label>
                                        <p class="text-black font-medium">${project.no_kontrak}</p>
                                    </div>
                                    ` : ''}
                                    ${project.pax ? `
                                    <div>
                                        <label class="text-sm font-medium text-gray-600">Pax:</label>
                                        <p class="text-black font-medium">${project.pax} orang</p>
                                    </div>
                                    ` : ''}
                                </div>
                            </div>

                            <!-- Financial Info -->
                            <div class="bg-gray-50 rounded-xl p-4">
                                <h4 class="text-lg font-semibold text-blue-600 mb-4">Informasi Keuangan</h4>
                                <div class="space-y-4">
                                    <div class="bg-white rounded-lg p-3 border border-gray-200">
                                        <label class="text-sm font-medium text-gray-600">Total Nilai Project</label>
                                        <p class="text-2xl font-bold text-blue-600">Rp ${project.total_price ? new Intl.NumberFormat('id-ID').format(project.total_price) : '0'}</p>
                                    </div>
                                    <div class="bg-white rounded-lg p-3 border border-gray-200">
                                        <label class="text-sm font-medium text-gray-600">Sudah Terbayar</label>
                                        <p class="text-2xl font-bold text-blue-600">Rp ${project.paid_amount ? new Intl.NumberFormat('id-ID').format(project.paid_amount) : '0'}</p>
                                    </div>
                                    <div class="bg-white rounded-lg p-3 border border-gray-200">
                                        <label class="text-sm font-medium text-gray-600">Status Pembayaran</label>
                                        <span class="block mt-1 px-3 py-1 rounded-full text-sm font-medium ${project.is_paid ? 'bg-blue-600 text-white' : 'bg-blue-500 text-white'}">
                                            ${project.is_paid ? '✓ Lunas' : '⏳ Belum Lunas'}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Timeline Info -->
                            <div class="md:col-span-2 bg-gray-50 rounded-xl p-4">
                                <h4 class="text-lg font-semibold text-blue-600 mb-4">Timeline</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="bg-white rounded-lg p-3 border border-gray-200">
                                        <label class="text-sm font-medium text-gray-600">Dibuat</label>
                                        <p class="text-blue-600 font-medium">${new Date(project.created_at).toLocaleDateString('id-ID')}</p>
                                    </div>
                                    ${project.closing_date ? `
                                    <div class="bg-white rounded-lg p-3 border border-gray-200">
                                        <label class="text-sm font-medium text-gray-600">Tanggal Closing</label>
                                        <p class="text-blue-600 font-medium">${new Date(project.closing_date).toLocaleDateString('id-ID')}</p>
                                    </div>
                                    ` : ''}
                                    <div class="bg-white rounded-lg p-3 border border-gray-200">
                                        <label class="text-sm font-medium text-gray-600">Terakhir Update</label>
                                        <p class="text-blue-600 font-medium">${new Date(project.updated_at).toLocaleDateString('id-ID')}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
                            <button onclick="closeProjectModal()" 
                                class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors font-medium">
                                Tutup
                            </button>
                            <a href="/admin/orders/${project.id}/edit" 
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                                Edit Project
                            </a>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeProjectModal();
                }
            });
        }

        // Function untuk menutup modal
        function closeProjectModal() {
            const modal = document.querySelector('.fixed.inset-0.bg-black.bg-opacity-50');
            if (modal) {
                modal.remove();
            }
        }

        // Close modal with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeProjectModal();
            }
        });
    </script>
@endsection
