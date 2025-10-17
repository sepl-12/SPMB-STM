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
                    {{ setting('contact_address', 'Jl. Pendidikan No. 123, Jakarta') }}
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
                <div class="text-gray-600 leading-relaxed space-y-1">
                    @if(setting('contact_phone'))
                    <p>
                        <a href="tel:{{ setting('contact_phone') }}" class="hover:text-green-500 transition-colors">
                            {{ setting('contact_phone') }}
                        </a>
                    </p>
                    @endif
                    @if(setting('contact_whatsapp'))
                    <p>
                        <a href="https://wa.me/{{ setting('contact_whatsapp') }}" target="_blank" rel="noopener noreferrer" class="hover:text-green-500 transition-colors">
                            WhatsApp: +{{ setting('contact_whatsapp') }}
                        </a>
                    </p>
                    @endif
                </div>
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
                    <a href="mailto:{{ setting('contact_email') }}" class="hover:text-green-500 transition-colors">
                        {{ setting('contact_email', 'info@sekolah.com') }}
                    </a>
                </p>
            </div>
        </div>
    </div>
</section>
