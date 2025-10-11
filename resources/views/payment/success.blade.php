<x-layout>
    <x-slot name="title">Pembayaran Berhasil - PPDB SMK</x-slot>

    <div class="min-h-screen bg-gradient-to-br from-green-50 via-white to-blue-50 py-6 sm:py-12">
        <div class="max-w-3xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8">
            
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
                                <li>Anda akan menerima notifikasi jika pembayaran sudah diverifikasi</li>
                                <li>Pantau status pendaftaran Anda melalui dashboard</li>
                                <li>Siapkan dokumen yang diperlukan untuk tahap selanjutnya</li>
                            </ol>
                        </div>
                    </div>
                </div>

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
