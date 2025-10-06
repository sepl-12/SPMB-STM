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
