<x-layout>
    <x-slot name="title">Pembayaran - PPDB SMK</x-slot>

    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-green-50 py-6 sm:py-12">
        <div class="max-w-4xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8">
            
            <!-- Payment Card -->
            <div class="bg-white rounded-xl sm:rounded-2xl shadow-xl p-6 sm:p-8">
                
                <!-- Header -->
                <div class="text-center mb-6 sm:mb-8">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 sm:h-16 sm:w-16 rounded-full bg-blue-100 mb-4">
                        <svg class="h-8 w-8 sm:h-10 sm:w-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">
                        Pembayaran Biaya Pendaftaran
                    </h1>
                    <p class="text-sm sm:text-base text-gray-600">
                        Selesaikan pembayaran untuk melanjutkan proses pendaftaran
                    </p>
                </div>

                <!-- Applicant Info -->
                <div class="bg-gradient-to-r from-blue-50 to-green-50 rounded-xl p-4 sm:p-6 mb-6">
                    <h2 class="font-semibold text-gray-900 mb-4 text-sm sm:text-base">Informasi Pendaftar</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 text-sm">
                        <div>
                            <p class="text-gray-600">Nomor Pendaftaran</p>
                            <p class="font-semibold text-gray-900">{{ $applicant->registration_number }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Nama Lengkap</p>
                            <p class="font-semibold text-gray-900">{{ $applicant->applicant_full_name }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Gelombang</p>
                            <p class="font-semibold text-gray-900">{{ $applicant->wave->wave_name }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Tanggal Daftar</p>
                            <p class="font-semibold text-gray-900">{{ $applicant->registered_datetime->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="border-t border-b border-gray-200 py-6 mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-gray-600">Biaya Pendaftaran</span>
                        <span class="text-xl sm:text-2xl font-bold text-gray-900">
                            Rp {{ number_format($applicant->wave->registration_fee_amount, 0, ',', '.') }}
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-gray-500">
                        * Biaya sudah termasuk biaya administrasi
                    </p>
                </div>

                <!-- Payment Instructions -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
                    <div class="flex gap-3">
                        <svg class="h-5 w-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1 text-sm">Instruksi Pembayaran</h3>
                            <ul class="list-disc list-inside space-y-1 text-xs text-gray-700">
                                <li>Klik tombol "Bayar Sekarang" di bawah ini</li>
                                <li>Pilih metode pembayaran yang Anda inginkan</li>
                                <li>Ikuti instruksi pembayaran yang diberikan</li>
                                <li>Setelah pembayaran berhasil, status akan otomatis terupdate</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Payment Button -->
                <div class="text-center">
                    <button 
                        id="pay-button"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg font-semibold hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg hover:shadow-xl"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Bayar Sekarang
                    </button>
                </div>

                <!-- Back Button -->
                <div class="mt-6 text-center">
                    <a href="{{ route('registration.success', $applicant->registration_number) }}" class="text-sm text-gray-600 hover:text-gray-900 underline">
                        Kembali ke Halaman Konfirmasi
                    </a>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="mt-6 text-center px-2">
                <div class="flex items-center justify-center gap-2 text-sm text-gray-600">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    <span>Pembayaran Anda aman dan terenkripsi dengan Midtrans</span>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="{{ config('payment.midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
            data-client-key="{{ config('payment.midtrans.client_key') }}">
    </script>
    <script>
        const payButton = document.getElementById('pay-button');
        const snapToken = @json($snapToken);

        payButton.addEventListener('click', function() {
            snap.pay(snapToken, {
                onSuccess: function(result) {
                    console.log('Payment success:', result);
                    window.location.href = '{{ route("payment.status", $applicant->registration_number) }}';
                },
                onPending: function(result) {
                    console.log('Payment pending:', result);
                    window.location.href = '{{ route("payment.status", $applicant->registration_number) }}';
                },
                onError: function(result) {
                    console.log('Payment error:', result);
                    alert('Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi.');
                },
                onClose: function() {
                    console.log('Payment popup closed');
                }
            });
        });
    </script>
    @endpush
</x-layout>
