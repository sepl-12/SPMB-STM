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
            @foreach($settings->faq_items_json ?? [] as $faq)
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <button class="faq-toggle w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                    <span class="font-semibold text-gray-900 text-lg">{{ $faq['question'] }}</span>
                    <svg class="faq-icon w-5 h-5 text-gray-500 transition-transform duration-300 ease-in-out" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="faq-content faq-closed transition-all duration-300 ease-in-out overflow-hidden" style="max-height: 0;">
                    <div class="px-6 pb-5">
                        <p class="text-gray-600 leading-relaxed">
                            {{ $faq['answer'] }}
                        </p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
