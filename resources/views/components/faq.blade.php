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
            @php
                $faqs = [
                    [
                        'question' => 'Apa saja persyaratan pendaftaran?',
                        'answer' => 'Persyaratan pendaftaran meliputi mengisi formulir online, pas foto 3x4 (2 lembar), fotocopy Kartu Keluarga (KK), fotocopy Akta Kelahiran, fotocopy kartu/surat keterangan NISN, dan mengikuti test seleksi.'
                    ],
                    [
                        'question' => 'Berapa biaya pendaftaran?',
                        'answer' => 'Biaya pendaftaran adalah Rp 300.000 yang dapat dibayarkan melalui transfer bank atau pembayaran langsung ke sekolah. Biaya ini sudah termasuk biaya test seleksi dan formulir pendaftaran.'
                    ],
                    [
                        'question' => 'Apakah ada jalur prestasi?',
                        'answer' => 'Ya, kami menyediakan jalur prestasi untuk siswa yang memiliki prestasi akademik atau non-akademik. Calon siswa dengan prestasi dapat melampirkan sertifikat atau piagam penghargaan saat mendaftar untuk mendapatkan nilai tambahan.'
                    ],
                    [
                        'question' => 'Kapan pengumuman hasil seleksi?',
                        'answer' => 'Pengumuman hasil seleksi akan diumumkan 7 hari setelah test seleksi dilaksanakan. Hasil dapat dilihat secara online melalui website ini dengan memasukkan nomor pendaftaran Anda.'
                    ],
                    [
                        'question' => 'Bagaimana jika saya mengalami kesulitan saat mendaftar?',
                        'answer' => 'Jika mengalami kesulitan saat mendaftar, Anda dapat menghubungi tim support kami melalui WhatsApp, telepon, atau email yang tertera di bagian kontak. Tim kami siap membantu Anda dari hari Senin - Jumat pukul 08:00 - 16:00 WIB.'
                    ]
                ];
            @endphp
            
            @foreach($faqs as $faq)
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <button class="faq-toggle w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                    <span class="font-semibold text-gray-900 text-lg">{{ $faq['question'] }}</span>
                    <svg class="faq-icon w-5 h-5 text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="faq-content hidden px-6 pb-5">
                    <p class="text-gray-600 leading-relaxed">
                        {{ $faq['answer'] }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
