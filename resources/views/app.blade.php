<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PPDB SMK Muh 1 - Penerimaan Peserta Didik Baru Online 2025/2026</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="overflow-x-hidden">
    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-md shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <a href="/" class="flex items-center gap-3 text-gray-900 hover:text-gray-700 transition-colors">
                    <img src="{{ asset('Logo STM.png') }}" alt="Logo SMK Muh 1" class="h-10 w-auto">
                    <span class="font-semibold text-lg hidden sm:block">PPDB SMK Muh 1</span>
                </a>
                
                <!-- Desktop Navigation -->
                <nav class="hidden lg:block">
                    <ul class="flex items-center gap-8">
                        <li><a href="#tentang" class="text-gray-700 hover:text-green-500 font-medium transition-colors">Tentang</a></li>
                        <li><a href="#alur-pendaftaran" class="text-gray-700 hover:text-green-500 font-medium transition-colors">Alur Pendaftaran</a></li>
                        <li><a href="#syarat" class="text-gray-700 hover:text-green-500 font-medium transition-colors">Syarat</a></li>
                        <li><a href="#faq" class="text-gray-700 hover:text-green-500 font-medium transition-colors">FAQ</a></li>
                        <li><a href="#kontak" class="text-gray-700 hover:text-green-500 font-medium transition-colors">Kontak</a></li>
                    </ul>
                </nav>
                
                <!-- CTA Button -->
                <a href="/daftar" class="hidden lg:inline-flex items-center px-6 py-2.5 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-green-500/30">
                    Daftar Sekarang
                </a>
                
                <!-- Mobile Menu Toggle -->
                <button id="mobile-menu-toggle" class="lg:hidden p-2 text-gray-700 hover:text-gray-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden lg:hidden border-t border-gray-200 bg-white">
            <nav class="px-4 py-4 space-y-2">
                <a href="#tentang" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">Tentang</a>
                <a href="#alur-pendaftaran" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">Alur Pendaftaran</a>
                <a href="#syarat" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">Syarat</a>
                <a href="#faq" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">FAQ</a>
                <a href="#kontak" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">Kontak</a>
                <a href="/daftar" class="block px-4 py-2 bg-green-500 text-white text-center font-semibold rounded-lg hover:bg-green-600 transition-colors">Daftar Sekarang</a>
            </nav>
        </div>
    </header>
    
    <!-- Hero Section -->
    <section class="relative min-h-screen flex items-center justify-center overflow-hidden">
        <!-- Background Image with Placeholder -->
        <div class="absolute inset-0 z-0 hero-bg-placeholder">
            @if(file_exists(public_path('hero-bg.jpg')))
                <img src="{{ asset('hero-bg.jpg') }}" alt="SMK Muhammadiyah 1 Sangatta Utara" class="w-full h-full object-cover">
            @else
                <!-- Placeholder pattern jika gambar belum ada -->
                <div class="w-full h-full bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-700"></div>
            @endif
        </div>
        
        <!-- Overlay -->
        <div class="absolute inset-0 bg-gradient-to-b from-black/50 to-black/70 z-10"></div>
        
        <!-- Wave SVG -->
        <div class="absolute bottom-0 left-0 w-full z-20">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto">
                <path d="M0,64L80,69.3C160,75,320,85,480,80C640,75,800,53,960,48C1120,43,1280,53,1360,58.7L1440,64L1440,120L1360,120C1280,120,1120,120,960,120C800,120,640,120,480,120C320,120,160,120,80,120L0,120Z" fill="white"/>
            </svg>
        </div>
        
        <!-- Hero Content -->
        <div class="relative z-30 text-center text-white max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold leading-tight mb-6 drop-shadow-lg">
                Penerimaan Peserta Didik Baru Online 2025/2026
            </h1>
            <p class="text-lg sm:text-xl lg:text-2xl leading-relaxed mb-10 text-white/95 max-w-3xl mx-auto">
                SMK Muhammadiyah 1 Sangatta Utara membuka pendaftaran siswa baru. Daftar sekarang juga dan jadi bagian dari kami.
            </p>
            
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="/daftar" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 bg-green-500 hover:bg-green-600 text-white font-semibold text-lg rounded-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-green-500/40">
                    Daftar Sekarang
                </a>
                <a href="#alur-pendaftaran" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 bg-white hover:bg-gray-50 text-gray-900 font-semibold text-lg rounded-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                    <span>Lihat Alur Pendaftaran</span>
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>
        </div>
    </section>
    
    <!-- Why Choose Us Section -->
    <section id="tentang" class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                    Kenapa Memilih Sekolah Kami?
                </h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Kami berkomitmen untuk memberikan pendidikan terbaik bagi putra-putri Anda dengan berbagai keunggulan.
                </p>
            </div>
            
            <!-- Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                <!-- Feature 1: Visi & Misi Unggul -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-lg transition-shadow duration-300 border border-gray-100">
                    <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke-width="2"/>
                            <circle cx="12" cy="12" r="6" stroke-width="2"/>
                            <circle cx="12" cy="12" r="2" fill="currentColor"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">
                        Visi & Misi Unggul
                    </h3>
                    <p class="text-gray-600 leading-relaxed">
                        Menjadi lembaga pendidikan terdepan yang menghasilkan generasi cerdas, berkarakter, dan berdaya saing global.
                    </p>
                </div>
                
                <!-- Feature 2: Fasilitas Modern -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-lg transition-shadow duration-300 border border-gray-100">
                    <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">
                        Fasilitas Modern
                    </h3>
                    <p class="text-gray-600 leading-relaxed">
                        Kurikulum terintegrasi, fasilitas modern, dan lingkungan belajar yang mendukung untuk memaksimalkan potensi siswa.
                    </p>
                </div>
                
                <!-- Feature 3: Program Berprestasi -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-lg transition-shadow duration-300 border border-gray-100">
                    <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">
                        Program Berprestasi
                    </h3>
                    <p class="text-gray-600 leading-relaxed">
                        Program bilingual, ekstrakurikuler beragam (robotik, seni, olahraga), dan program pengembangan kepemimpinan.
                    </p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Alur Pendaftaran Section -->
    <section id="alur-pendaftaran" class="py-20 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                    Alur Pendaftaran PPDB
                </h2>
                <p class="text-lg text-gray-600">
                    Ikuti 4 langkah mudah untuk mendaftar di sekolah kami.
                </p>
            </div>
            
            <!-- Timeline -->
            <div class="relative">
                <!-- Vertical Line -->
                <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gray-200 hidden sm:block"></div>
                
                <!-- Step 1 -->
                <div class="relative flex items-start mb-12 last:mb-0">
                    <!-- Icon Circle -->
                    <div class="flex-shrink-0 w-12 h-12 bg-green-500 rounded-full flex items-center justify-center shadow-lg z-10">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </div>
                    
                    <!-- Content Card -->
                    <div class="ml-6 flex-1 bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">
                            1. Buat Akun & Isi Formulir
                        </h3>
                        <p class="text-gray-600 leading-relaxed">
                            Calon siswa membuat akun dan mengisi formulir pendaftaran secara online dengan data yang lengkap dan benar.
                        </p>
                    </div>
                </div>
                
                <!-- Step 2 -->
                <div class="relative flex items-start mb-12 last:mb-0">
                    <!-- Icon Circle -->
                    <div class="flex-shrink-0 w-12 h-12 bg-green-500 rounded-full flex items-center justify-center shadow-lg z-10">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    
                    <!-- Content Card -->
                    <div class="ml-6 flex-1 bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">
                            2. Seleksi Berkas
                        </h3>
                        <p class="text-gray-600 leading-relaxed">
                            Panitia PPDB akan melakukan verifikasi dan validasi berkas pendaftaran yang telah diunggah oleh calon siswa.
                        </p>
                    </div>
                </div>
                
                <!-- Step 3 -->
                <div class="relative flex items-start mb-12 last:mb-0">
                    <!-- Icon Circle -->
                    <div class="flex-shrink-0 w-12 h-12 bg-green-500 rounded-full flex items-center justify-center shadow-lg z-10">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    
                    <!-- Content Card -->
                    <div class="ml-6 flex-1 bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">
                            3. Pengumuman Hasil
                        </h3>
                        <p class="text-gray-600 leading-relaxed">
                            Hasil seleksi akan diumumkan secara online melalui website ini. Calon siswa dapat melihat status kelulusan.
                        </p>
                    </div>
                </div>
                
                <!-- Step 4 -->
                <div class="relative flex items-start mb-12 last:mb-0">
                    <!-- Icon Circle -->
                    <div class="flex-shrink-0 w-12 h-12 bg-green-500 rounded-full flex items-center justify-center shadow-lg z-10">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    
                    <!-- Content Card -->
                    <div class="ml-6 flex-1 bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">
                            4. Daftar Ulang
                        </h3>
                        <p class="text-gray-600 leading-relaxed">
                            Siswa yang dinyatakan lulus seleksi diwajibkan melakukan daftar ulang sesuai jadwal yang telah ditentukan.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- CTA Button -->
            <div class="text-center mt-12">
                <a href="/daftar" class="inline-flex items-center justify-center px-8 py-4 bg-green-500 hover:bg-green-600 text-white font-semibold text-lg rounded-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-green-500/40">
                    Mulai Pendaftaran Sekarang
                </a>
            </div>
        </div>
    </section>
    
    <!-- Informasi Gelombang Pendaftaran Section -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                    Informasi Gelombang Pendaftaran
                </h2>
                <p class="text-lg text-gray-600">
                    Berikut adalah jadwal pendaftaran siswa baru untuk tahun ajaran 2025/2026.
                </p>
            </div>
            
            <!-- Timeline Progress Bar -->
            <div class="relative mb-12 hidden md:block">
                <div class="absolute top-1/2 left-0 right-0 h-0.5 bg-gray-300 -translate-y-1/2"></div>
                <div class="relative flex justify-between items-center max-w-4xl mx-auto">
                    <!-- Point 1 - Selesai -->
                    <div class="w-4 h-4 bg-gray-400 rounded-full ring-4 ring-white"></div>
                    <!-- Point 2 - Active -->
                    <div class="w-4 h-4 bg-green-500 rounded-full ring-4 ring-white"></div>
                    <!-- Point 3 - Upcoming -->
                    <div class="w-4 h-4 bg-blue-500 rounded-full ring-4 ring-white"></div>
                </div>
            </div>
            
            <!-- Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                <!-- Gelombang 1 - Selesai -->
                <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-200 hover:shadow-lg transition-all duration-300">
                    <!-- Icon -->
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-6 mx-auto">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    
                    <!-- Title -->
                    <h3 class="text-2xl font-bold text-gray-900 text-center mb-3">
                        Gelombang 1
                    </h3>
                    
                    <!-- Date -->
                    <p class="text-gray-600 text-center mb-6">
                        1 Januari - 28 Februari 2025
                    </p>
                    
                    <!-- Status Badge -->
                    <div class="flex justify-center">
                        <span class="inline-flex items-center px-6 py-2 bg-gray-100 text-gray-600 font-semibold rounded-full text-sm">
                            Selesai
                        </span>
                    </div>
                </div>
                
                <!-- Gelombang 2 - Dibuka (Active) -->
                <div class="bg-white rounded-2xl p-8 shadow-lg border-2 border-green-500 hover:shadow-xl transition-all duration-300 relative">
                    <!-- Active Badge -->
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                        <span class="inline-flex items-center px-4 py-1 bg-green-500 text-white font-semibold rounded-full text-xs">
                            Sedang Berlangsung
                        </span>
                    </div>
                    
                    <!-- Icon -->
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6 mx-auto">
                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    
                    <!-- Title -->
                    <h3 class="text-2xl font-bold text-gray-900 text-center mb-3">
                        Gelombang 2
                    </h3>
                    
                    <!-- Date -->
                    <p class="text-gray-600 text-center mb-6">
                        1 Maret - 30 April 2025
                    </p>
                    
                    <!-- Status Badge -->
                    <div class="flex justify-center">
                        <span class="inline-flex items-center px-6 py-2 bg-green-500 text-white font-semibold rounded-full text-sm">
                            Dibuka
                        </span>
                    </div>
                </div>
                
                <!-- Gelombang 3 - Akan Datang -->
                <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-200 hover:shadow-lg transition-all duration-300">
                    <!-- Icon -->
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-6 mx-auto">
                        <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    
                    <!-- Title -->
                    <h3 class="text-2xl font-bold text-gray-900 text-center mb-3">
                        Gelombang 3
                    </h3>
                    
                    <!-- Date -->
                    <p class="text-gray-600 text-center mb-6">
                        1 Mei - 30 Juni 2025
                    </p>
                    
                    <!-- Status Badge -->
                    <div class="flex justify-center">
                        <span class="inline-flex items-center px-6 py-2 border-2 border-blue-500 text-blue-500 font-semibold rounded-full text-sm">
                            Akan Datang
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Dokumen yang Diperlukan Section -->
    <section id="syarat" class="py-20 bg-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Card Container with Orange Border -->
            <div class="bg-white rounded-3xl shadow-lg border-t-4 border-orange-500 p-8 md:p-12">
                <!-- Icon and Title -->
                <div class="text-center mb-12">
                    <div class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center mb-6 mx-auto">
                        <svg class="w-10 h-10 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">
                        Dokumen yang Diperlukan
                    </h2>
                </div>
                
                <!-- Documents Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Document 1 -->
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-bold text-lg">1</span>
                        </div>
                        <div class="flex-1 pt-1">
                            <p class="text-gray-700 text-lg">Mengisi formulir pendaftaran</p>
                        </div>
                    </div>
                    
                    <!-- Document 2 -->
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-bold text-lg">2</span>
                        </div>
                        <div class="flex-1 pt-1">
                            <p class="text-gray-700 text-lg">Pas foto ukuran 3x4 (2 lembar)</p>
                        </div>
                    </div>
                    
                    <!-- Document 3 -->
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-bold text-lg">3</span>
                        </div>
                        <div class="flex-1 pt-1">
                            <p class="text-gray-700 text-lg">Fotocopy Kartu Keluarga (KK)</p>
                        </div>
                    </div>
                    
                    <!-- Document 4 -->
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-bold text-lg">4</span>
                        </div>
                        <div class="flex-1 pt-1">
                            <p class="text-gray-700 text-lg">Fotocopy Akta Kelahiran</p>
                        </div>
                    </div>
                    
                    <!-- Document 5 -->
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-bold text-lg">5</span>
                        </div>
                        <div class="flex-1 pt-1">
                            <p class="text-gray-700 text-lg">Fotocopy Kartu/surat keterangan NISN</p>
                        </div>
                    </div>
                    
                    <!-- Document 6 -->
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-bold text-lg">6</span>
                        </div>
                        <div class="flex-1 pt-1">
                            <p class="text-gray-700 text-lg">Mengikuti test seleksi</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- FAQ Section -->
    <section id="faq" class="py-20 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                    Pertanyaan yang Sering Diajukan (FAQ)
                </h2>
                <p class="text-lg text-gray-600">
                    Temukan jawaban atas pertanyaan umum seputar proses pendaftaran siswa baru di sekolah kami.
                </p>
            </div>
            
            <!-- FAQ Accordion -->
            <div class="space-y-4">
                <!-- FAQ Item 1 -->
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <button class="faq-toggle w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <span class="font-semibold text-gray-900 text-lg">Apa saja persyaratan pendaftaran?</span>
                        <svg class="faq-icon w-5 h-5 text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div class="faq-content hidden px-6 pb-5">
                        <p class="text-gray-600 leading-relaxed">
                            Persyaratan pendaftaran meliputi mengisi formulir online, pas foto 3x4 (2 lembar), fotocopy Kartu Keluarga (KK), fotocopy Akta Kelahiran, fotocopy kartu/surat keterangan NISN, dan mengikuti test seleksi.
                        </p>
                    </div>
                </div>
                
                <!-- FAQ Item 2 -->
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <button class="faq-toggle w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <span class="font-semibold text-gray-900 text-lg">Berapa biaya pendaftaran?</span>
                        <svg class="faq-icon w-5 h-5 text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div class="faq-content hidden px-6 pb-5">
                        <p class="text-gray-600 leading-relaxed">
                            Biaya pendaftaran adalah Rp 300.000 yang dapat dibayarkan melalui transfer bank atau pembayaran langsung ke sekolah. Biaya ini sudah termasuk biaya test seleksi dan formulir pendaftaran.
                        </p>
                    </div>
                </div>
                
                <!-- FAQ Item 3 -->
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <button class="faq-toggle w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <span class="font-semibold text-gray-900 text-lg">Apakah ada jalur prestasi?</span>
                        <svg class="faq-icon w-5 h-5 text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div class="faq-content hidden px-6 pb-5">
                        <p class="text-gray-600 leading-relaxed">
                            Ya, kami menyediakan jalur prestasi untuk siswa yang memiliki prestasi akademik atau non-akademik. Calon siswa dengan prestasi dapat melampirkan sertifikat atau piagam penghargaan saat mendaftar untuk mendapatkan nilai tambahan.
                        </p>
                    </div>
                </div>
                
                <!-- FAQ Item 4 -->
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <button class="faq-toggle w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <span class="font-semibold text-gray-900 text-lg">Kapan pengumuman hasil seleksi?</span>
                        <svg class="faq-icon w-5 h-5 text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div class="faq-content hidden px-6 pb-5">
                        <p class="text-gray-600 leading-relaxed">
                            Pengumuman hasil seleksi akan diumumkan 7 hari setelah test seleksi dilaksanakan. Hasil dapat dilihat secara online melalui website ini dengan memasukkan nomor pendaftaran Anda.
                        </p>
                    </div>
                </div>
                
                <!-- FAQ Item 5 -->
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <button class="faq-toggle w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <span class="font-semibold text-gray-900 text-lg">Bagaimana jika saya mengalami kesulitan saat mendaftar?</span>
                        <svg class="faq-icon w-5 h-5 text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div class="faq-content hidden px-6 pb-5">
                        <p class="text-gray-600 leading-relaxed">
                            Jika mengalami kesulitan saat mendaftar, Anda dapat menghubungi tim support kami melalui WhatsApp, telepon, atau email yang tertera di bagian kontak. Tim kami siap membantu Anda dari hari Senin - Jumat pukul 08:00 - 16:00 WIB.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Contact Us Section -->
    <section id="kontak" class="py-20 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                    Hubungi Kami
                </h2>
                <p class="text-lg text-gray-600">
                    Punya pertanyaan? Jangan ragu untuk menghubungi kami melalui informasi di bawah ini.
                </p>
            </div>
            
            <!-- Contact Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                <!-- Alamat Card -->
                <div class="bg-white rounded-2xl p-8 border border-gray-200 hover:shadow-lg transition-shadow text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6 mx-auto">
                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">
                        Alamat
                    </h3>
                    <p class="text-gray-600 leading-relaxed">
                        Jl. Pendidikan No. 123, Kota Ilmu, Indonesia
                    </p>
                </div>
                
                <!-- Telepon & WhatsApp Card -->
                <div class="bg-white rounded-2xl p-8 border border-gray-200 hover:shadow-lg transition-shadow text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6 mx-auto">
                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">
                        Telepon & WhatsApp
                    </h3>
                    <p class="text-gray-600 leading-relaxed">
                        (021) 123-4567
                    </p>
                </div>
                
                <!-- Email Card -->
                <div class="bg-white rounded-2xl p-8 border border-gray-200 hover:shadow-lg transition-shadow text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6 mx-auto">
                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">
                        Email
                    </h3>
                    <p class="text-gray-600 leading-relaxed break-words">
                        info@smkmuh1sangatta.sch.id
                    </p>
                </div>
            </div>
        </div>
    </section>
    
    <script>
        // Mobile menu toggle
        const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');
        
        mobileMenuToggle.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
        
        // Close mobile menu when clicking a link
        const mobileMenuLinks = mobileMenu.querySelectorAll('a');
        mobileMenuLinks.forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.add('hidden');
            });
        });
        
        // FAQ Accordion
        const faqToggles = document.querySelectorAll('.faq-toggle');
        faqToggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const content = this.nextElementSibling;
                const icon = this.querySelector('.faq-icon');
                
                // Toggle current FAQ
                content.classList.toggle('hidden');
                icon.classList.toggle('rotate-180');
            });
        });
        
        // Smooth scroll
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
    </script>
</body>
</html>
