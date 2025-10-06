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
    
    <!-- Wave SVG (Removed to fix gray line issue) -->
    <div class="absolute bottom-0 left-0 w-full z-20 -mb-1">
        <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto block">
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
