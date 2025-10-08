<x-layout>
    <x-slot name="title">Pendaftaran Ditutup - PPDB SMK</x-slot>

    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50 py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Closed Card -->
            <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
                
                <!-- Icon -->
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gray-100 mb-6">
                    <svg class="h-10 w-10 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>

                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    Pendaftaran Ditutup
                </h1>
                <p class="text-gray-600 mb-8">
                    Maaf, saat ini belum ada gelombang pendaftaran yang sedang aktif.
                </p>

                <!-- Info Box -->
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-8 text-left">
                    <div class="flex gap-3">
                        <svg class="h-6 w-6 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-2">Informasi Penting</h3>
                            <ul class="space-y-2 text-sm text-gray-700">
                                <li>• Silakan cek kembali website ini secara berkala untuk informasi gelombang pendaftaran selanjutnya</li>
                                <li>• Anda dapat menghubungi panitia PPDB untuk informasi lebih lanjut</li>
                                <li>• Pastikan Anda sudah menyiapkan dokumen-dokumen yang diperlukan</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a 
                        href="{{ route('home') }}"
                        class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg font-medium hover:from-green-700 hover:to-green-800 transition-all duration-200 shadow-lg hover:shadow-xl"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Kembali ke Beranda
                    </a>
                    <a 
                        href="#"
                        class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors duration-200"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Hubungi Panitia
                    </a>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="mt-8 text-center">
                <p class="text-gray-600 text-sm">
                    Informasi lebih lanjut: 
                    <a href="tel:+6281234567890" class="text-green-600 font-medium hover:underline">+62 812-3456-7890</a>
                    atau email 
                    <a href="mailto:info@smkmuh1.sch.id" class="text-green-600 font-medium hover:underline">info@smkmuh1.sch.id</a>
                </p>
            </div>
        </div>
    </div>
</x-layout>
