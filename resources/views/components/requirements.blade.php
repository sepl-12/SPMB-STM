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
                @foreach([
                    'Mengisi formulir pendaftaran',
                    'Pas foto ukuran 3x4 (2 lembar)',
                    'Fotocopy Kartu Keluarga (KK)',
                    'Fotocopy Akta Kelahiran',
                    'Fotocopy Kartu/surat keterangan NISN',
                    'Mengikuti test seleksi'
                ] as $index => $document)
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold text-lg">{{ $index + 1 }}</span>
                    </div>
                    <div class="flex-1 pt-1">
                        <p class="text-gray-700 text-lg">{{ $document }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
