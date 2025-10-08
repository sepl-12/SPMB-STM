<x-layout>
    <x-slot name="title">Pendaftaran Berhasil - PPDB SMK</x-slot>

    <div class="min-h-screen bg-gradient-to-br from-green-50 via-white to-blue-50 py-6 sm:py-12">
        <div class="max-w-3xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8">
            
            <!-- Success Card -->
            <div class="bg-white rounded-xl sm:rounded-2xl shadow-xl p-6 sm:p-8 text-center">
                
                <!-- Success Icon -->
                <div class="mx-auto flex items-center justify-center h-14 w-14 sm:h-16 sm:w-16 rounded-full bg-green-100 mb-4 sm:mb-6">
                    <svg class="h-8 w-8 sm:h-10 sm:w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">
                    Pendaftaran Berhasil!
                </h1>
                <p class="text-sm sm:text-base text-gray-600 mb-6 sm:mb-8 px-2">
                    Selamat! Data pendaftaran Anda telah berhasil kami terima.
                </p>

                <!-- Registration Info -->
                <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-xl p-4 sm:p-6 mb-6 sm:mb-8">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 text-left">
                        <div>
                            <p class="text-xs sm:text-sm text-gray-600 mb-1">Nomor Pendaftaran</p>
                            <p class="text-base sm:text-lg font-bold text-gray-900 break-all">{{ $applicant->registration_number }}</p>
                        </div>
                        <div>
                            <p class="text-xs sm:text-sm text-gray-600 mb-1">Nama Lengkap</p>
                            <p class="text-base sm:text-lg font-bold text-gray-900">{{ $applicant->applicant_full_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs sm:text-sm text-gray-600 mb-1">Gelombang</p>
                            <p class="text-base sm:text-lg font-bold text-gray-900">{{ $applicant->wave->wave_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs sm:text-sm text-gray-600 mb-1">Tanggal Daftar</p>
                            <p class="text-base sm:text-lg font-bold text-gray-900">{{ $applicant->registered_datetime->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Important Notice -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 sm:p-6 mb-6 sm:mb-8 text-left">
                    <div class="flex gap-2 sm:gap-3">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-2 text-sm sm:text-base">Langkah Selanjutnya</h3>
                            <ol class="list-decimal list-inside space-y-1.5 sm:space-y-2 text-xs sm:text-sm text-gray-700">
                                <li>Simpan nomor pendaftaran Anda: <strong class="break-all">{{ $applicant->registration_number }}</strong></li>
                                <li>Lakukan pembayaran biaya pendaftaran sebesar <strong>Rp {{ number_format($applicant->wave->registration_fee_amount, 0, ',', '.') }}</strong></li>
                                <li>Upload bukti pembayaran melalui dashboard Anda</li>
                                <li>Tunggu konfirmasi dari panitia PPDB</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center">
                    <a 
                        href="#"
                        class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg font-medium hover:from-green-700 hover:to-green-800 transition-all duration-200 shadow-lg hover:shadow-xl text-sm sm:text-base"
                    >
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Lanjutkan Pembayaran
                    </a>
                    <a 
                        href="#"
                        class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors duration-200 text-sm sm:text-base"
                    >
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download Bukti Pendaftaran
                    </a>
                </div>

                <!-- Back to Home -->
                <div class="mt-4 sm:mt-6">
                    <a href="{{ route('home') }}" class="text-xs sm:text-sm text-gray-600 hover:text-gray-900 underline">
                        Kembali ke Halaman Utama
                    </a>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="mt-6 sm:mt-8 text-center px-2">
                <p class="text-gray-600 text-xs sm:text-sm">
                    Butuh bantuan? Hubungi kami di 
                    <a href="tel:+6281234567890" class="text-green-600 font-medium hover:underline">+62 812-3456-7890</a>
                    atau email 
                    <a href="mailto:info@smkmuh1.sch.id" class="text-green-600 font-medium hover:underline">info@smkmuh1.sch.id</a>
                </p>
            </div>
        </div>
    </div>
</x-layout>
