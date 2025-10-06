<!-- Alur Pendaftaran Section -->
<section id="alur-pendaftaran" class="py-20 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-16">
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                Alur Pendaftaran PPDB
            </h2>
            <p class="text-lg text-gray-600">
                Ikuti {{ count($settings->timeline_items_json ?? []) }} langkah mudah untuk mendaftar di sekolah kami.
            </p>
        </div>
        
        <!-- Timeline -->
        <div class="relative">
            <!-- Vertical Line -->
            <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gray-200 hidden sm:block"></div>
            
            @php
                $iconMap = [
                    'user-plus' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
                    'document' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                    'check-circle' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                    'currency' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                ];
            @endphp
            
            @foreach($settings->timeline_items_json ?? [] as $item)
            <div class="relative flex items-start mb-12 last:mb-0">
                <div class="flex-shrink-0 w-12 h-12 bg-green-500 rounded-full flex items-center justify-center shadow-lg z-10">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconMap[$item['icon']] ?? $iconMap['user-plus'] }}"/>
                    </svg>
                </div>
                <div class="ml-6 flex-1 bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $item['step'] }}. {{ $item['title'] }}</h3>
                    <p class="text-gray-600 leading-relaxed">
                        {{ $item['description'] }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- CTA Button -->
        <div class="text-center mt-12">
            <a href="{{ $settings->cta_button_url ?? '/daftar' }}" class="inline-flex items-center justify-center px-8 py-4 bg-green-500 hover:bg-green-600 text-white font-semibold text-lg rounded-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-green-500/40">
                Mulai Pendaftaran Sekarang
            </a>
        </div>
    </div>
</section>
