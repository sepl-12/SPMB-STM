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
                    <img src="{{ asset('logo-stm.png') }}" alt="Logo SMK Muh 1" class="h-10 w-auto">
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
