<x-layout>
    <x-slot name="title">Pembayaran Berhasil - PPDB SMK</x-slot>

    <div class="min-h-screen bg-gradient-to-br from-green-50 via-white to-blue-50 py-6 sm:py-12">
        <div class="max-w-3xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8">
            
            <!-- Security Notice -->
            <div class="bg-yellow-50 border-l-4 border-yellow-400 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-yellow-800 mb-1"> Link Aman</p>
                        <p class="text-xs text-yellow-700">
                            Halaman ini dilindungi dengan enkripsi. Link akan kadaluarsa dalam 7 hari untuk keamanan data Anda. 
                            Jangan bagikan link ini kepada orang lain.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Success Card -->
            <div class="bg-white rounded-xl sm:rounded-2xl shadow-xl p-6 sm:p-8 text-center">
                
                <!-- Success Icon with Animation -->
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 mb-6 animate-bounce">
                    <svg class="h-12 w-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    Pembayaran Berhasil!
                </h1>
                <p class="text-gray-600 mb-8">
                    Terima kasih, pembayaran Anda telah kami terima dan sedang diproses
                </p>

                <!-- Payment Receipt -->
                <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-xl p-6 mb-6 text-left">
                    <h2 class="font-semibold text-gray-900 mb-4 text-center">Bukti Pembayaran</h2>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Nomor Order</span>
                            <span class="font-semibold text-gray-900">{{ $latestPayment->merchant_order_code }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Nomor Pendaftaran</span>
                            <span class="font-semibold text-gray-900">{{ $applicant->registration_number }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Nama</span>
                            <span class="font-semibold text-gray-900">{{ $applicant->applicant_full_name }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Metode Pembayaran</span>
                            <span class="font-semibold text-gray-900">{{ $latestPayment->payment_method_name->label() }}</span>
                        </div>
                        
                        <div class="border-t border-gray-300 my-3"></div>
                        
                        <div class="flex justify-between">
                            <span class="font-semibold text-gray-900">Total Dibayar</span>
                            <span class="text-xl font-bold text-green-600">
                                Rp {{ number_format($latestPayment->paid_amount_total, 0, ',', '.') }}
                            </span>
                        </div>
                        
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Waktu Pembayaran</span>
                            <span class="font-semibold text-gray-900">{{ $latestPayment->status_updated_datetime->format('d M Y, H:i') }}</span>
                        </div>
                        
                        <div class="flex items-center justify-center gap-2 bg-green-100 rounded-lg p-3 mt-4">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-sm font-semibold text-green-800">Status: LUNAS</span>
                        </div>
                    </div>
                </div>

                <!-- Next Steps -->
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6 text-left">
                    <div class="flex gap-3">
                        <svg class="h-6 w-6 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-2">Langkah Selanjutnya</h3>
                            <ol class="list-decimal list-inside space-y-2 text-sm text-gray-700">
                                <li>Pembayaran Anda sedang diverifikasi oleh tim panitia</li>
                                <li>Siapkan dokumen yang diperlukan untuk tahap selanjutnya</li>
                            </ol>
                        </div>
                    </div>
                </div>

                @if($whatsappGroupUrl)
                <div class="bg-green-50 border border-green-200 rounded-xl p-6 mb-6 text-left">
                    <div class="flex gap-3">
                        <svg class="h-6 w-6 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-4 4v-4z"></path>
                        </svg>
                        <div class="w-full">
                            <h3 class="font-semibold text-gray-900 mb-2">Akses Grup WhatsApp</h3>
                            <p class="text-sm text-green-800 mb-4">
                                Pembayaran Anda sudah berhasil. Silakan gabung ke grup WhatsApp resmi untuk mendapatkan informasi lanjutan dari panitia.
                            </p>
                            <a
                                href="{{ $whatsappGroupUrl }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center justify-center px-5 py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors duration-200"
                            >
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M16.75 13.96c.25.13 1.47.72 1.7.8.23.08.4.13.45.2.05.07.05.78-.18 1.53-.23.75-1.35 1.44-1.83 1.53-.47.1-.87.14-1.4.02-.32-.08-.74-.24-1.27-.47-2.24-.97-3.7-3.24-3.82-3.4-.12-.16-.91-1.21-.91-2.32 0-1.1.58-1.64.78-1.87.2-.23.43-.29.58-.29.14 0 .29 0 .41.01.13.01.31-.05.48.36.18.43.61 1.49.67 1.6.05.11.09.24.02.39-.07.14-.11.24-.22.37-.11.13-.23.29-.33.39-.11.11-.22.22-.09.43.13.22.57.94 1.22 1.52.84.75 1.54.98 1.76 1.09.22.11.35.09.48-.05.13-.14.57-.67.73-.9.16-.23.31-.19.52-.11zM12.03 2C6.52 2 2.06 6.46 2.06 11.97c0 1.76.46 3.48 1.32 4.99L2 22l5.19-1.36a9.9 9.9 0 004.84 1.24h.01c5.5 0 9.96-4.46 9.96-9.97A9.96 9.96 0 0012.03 2zm0 18.13h-.01a8.17 8.17 0 01-4.16-1.14l-.3-.18-3.08.81.82-3-.2-.31a8.15 8.15 0 01-1.26-4.34c0-4.5 3.66-8.17 8.18-8.17a8.14 8.14 0 018.16 8.17c0 4.51-3.66 8.16-8.15 8.16z"></path>
                                </svg>
                                Gabung Grup WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 justify-center mb-6">
                    <button 
                        onclick="window.print()"
                        class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg font-medium hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Cetak Bukti Pembayaran
                    </button>
                    <a 
                        href="{{ route('registration.success', $applicant->registration_number) }}"
                        class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors duration-200"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Lihat Detail Pendaftaran
                    </a>
                </div>

                <!-- Back to Home -->
                <div class="text-center">
                    <a href="{{ route('home') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">
                        Kembali ke Halaman Utama
                    </a>
                </div>
            </div>

            <!-- Contact Support -->
            <div class="mt-6 text-center">
                <p class="text-gray-600 text-sm">
                    Butuh bantuan? Hubungi kami di 
                    <a href="tel:+6281234567890" class="text-blue-600 font-medium hover:underline">+62 812-3456-7890</a>
                    atau email 
                    <a href="mailto:info@smkmuh1.sch.id" class="text-blue-600 font-medium hover:underline">info@smkmuh1.sch.id</a>
                </p>
            </div>
        </div>
    </div>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</x-layout>
