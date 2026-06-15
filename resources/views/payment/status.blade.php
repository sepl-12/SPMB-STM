<x-layout>
    <x-slot name="title">Status Pembayaran - PPDB SMK</x-slot>

    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-green-50 py-6 sm:py-12">
        <div class="max-w-3xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8">
            
            <!-- Status Card -->
            <div class="bg-white rounded-xl sm:rounded-2xl shadow-xl p-6 sm:p-8">
                
                @if($latestPayment)
                    @php
                        $hasWhatsappGroupAccess = filled($whatsappGroupUrl);
                        $isPaid = $latestPayment->payment_status_name->isSuccess() || $hasWhatsappGroupAccess;
                        $isPending = $latestPayment->payment_status_name->isPending();
                        $isFailed = $latestPayment->payment_status_name->isFailed();
                        $isManualPayment = $latestPayment->payment_method_name === \App\Enum\PaymentMethod::MANUAL_TRANSFER;
                        $isPendingVerification = $latestPayment->payment_status_name === \App\Enum\PaymentStatus::PENDING_VERIFICATION;
                    @endphp

                    <!-- Status Icon -->
                    <div class="text-center mb-6">
                        @if($isPaid)
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                                <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">Pembayaran Berhasil!</h1>
                            <p class="text-gray-600">Terima kasih, pembayaran Anda telah kami terima</p>
                        @elseif($isPendingVerification)
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 mb-4">
                                <svg class="h-10 w-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">Menunggu Verifikasi Admin</h1>
                            <p class="text-gray-600">Bukti pembayaran Anda sedang diverifikasi oleh admin</p>
                            <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-800">
                                <p class="font-medium">📋 Proses Verifikasi</p>
                                <p class="mt-1">Admin akan memverifikasi bukti pembayaran Anda dalam waktu maksimal 1x24 jam. Anda akan diberitahu melalui email setelah pembayaran diverifikasi.</p>
                            </div>
                        @elseif($isPending)
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100 mb-4">
                                <svg class="h-10 w-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">Menunggu Pembayaran</h1>
                            <p class="text-gray-600">Pembayaran Anda sedang dalam proses</p>
                        @else
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                                <svg class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </div>
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">Pembayaran Gagal</h1>
                            <p class="text-gray-600">Terjadi kesalahan pada proses pembayaran</p>
                        @endif
                    </div>

                    <!-- Payment Details -->
                    <div class="bg-gray-50 rounded-xl p-6 mb-6">
                        <h2 class="font-semibold text-gray-900 mb-4">Detail Pembayaran</h2>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Nomor Order</span>
                                <span class="font-semibold text-gray-900">{{ $latestPayment->merchant_order_code }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Metode Pembayaran</span>
                                <span class="font-semibold text-gray-900">{{ $latestPayment->payment_method_name->label() }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Jumlah</span>
                                <span class="font-semibold text-gray-900">Rp {{ number_format($latestPayment->paid_amount_total, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status</span>
                                <span class="font-semibold {{ $isPaid ? 'text-green-600' : ($isPendingVerification ? 'text-blue-600' : ($isPending ? 'text-yellow-600' : 'text-red-600')) }}">
                                    {{ $isPaid ? 'Lunas' : ($isPendingVerification ? 'Menunggu Verifikasi' : ($isPending ? 'Menunggu' : 'Gagal')) }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Waktu Update</span>
                                <span class="font-semibold text-gray-900">{{ $latestPayment->status_updated_datetime->format('d M Y, H:i') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Applicant Info -->
                    <div class="bg-gradient-to-r from-blue-50 to-green-50 rounded-xl p-6 mb-6">
                        <h2 class="font-semibold text-gray-900 mb-4">Informasi Pendaftar</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                            <div>
                                <p class="text-gray-600">Nomor Pendaftaran</p>
                                <p class="font-semibold text-gray-900">{{ $applicant->registration_number }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Nama Lengkap</p>
                                <p class="font-semibold text-gray-900">{{ $applicant->applicant_full_name }}</p>
                            </div>
                        </div>
                    </div>

                    @if($whatsappGroupUrl)
                        <div class="bg-green-50 border border-green-200 rounded-xl p-6 mb-6">
                            <div class="flex items-start gap-3">
                                <svg class="h-6 w-6 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-4 4v-4z"></path>
                                </svg>
                                <div class="w-full">
                                    <h2 class="font-semibold text-gray-900 mb-2">Akses Grup WhatsApp</h2>
                                    <p class="text-sm text-green-800 mb-4">
                                        Anda sudah bisa masuk ke grup WhatsApp resmi panitia untuk menerima informasi lanjutan.
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
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        @if($isPaid)
                            <a
                                href="{{ route('registration.success', $applicant->registration_number) }}"
                                class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg font-medium hover:from-green-700 hover:to-green-800 transition-all duration-200 shadow-lg"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Lihat Detail Pendaftaran
                            </a>
                        @elseif($isPendingVerification)
                            <a
                                href="{{ route('home') }}"
                                class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-lg font-medium hover:from-gray-700 hover:to-gray-800 transition-all duration-200 shadow-lg"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                Kembali ke Beranda
                            </a>
                        @elseif($isPending)
                            <button
                                onclick="checkPaymentStatus()"
                                class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg font-medium hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg"
                            >
                                <svg class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Cek Status Pembayaran
                            </button>
                        @else
                            <a
                                href="{{ $applicant->payment_url }}"
                                class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg font-medium hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Coba Lagi
                            </a>
                        @endif
                    </div>

                @else
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gray-100 mb-4">
                            <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900 mb-2">Belum Ada Pembayaran</h1>
                        <p class="text-gray-600 mb-6">Anda belum melakukan pembayaran untuk pendaftaran ini</p>
                        <a 
                            href="{{ $applicant->payment_url }}"
                            class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg font-medium hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg"
                        >
                            Lakukan Pembayaran
                        </a>
                    </div>
                @endif

                <!-- Back to Home -->
                <div class="mt-6 text-center">
                    <a href="{{ route('home') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">
                        Kembali ke Halaman Utama
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($latestPayment && $latestPayment->payment_status_name->isPending() && !$isPendingVerification && blank($whatsappGroupUrl))
    @push('scripts')
    <script>
        function checkPaymentStatus() {
            const orderId = @json($latestPayment->merchant_order_code);

            fetch('{{ route("payment.check-status") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ order_id: orderId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload page to show updated status
                    window.location.reload();
                } else {
                    alert('Gagal mengecek status pembayaran. Silakan coba lagi.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengecek status pembayaran.');
            });
        }

        // Auto-check status every 30 seconds for non-manual payments
        setInterval(checkPaymentStatus, 30000);
    </script>
    @endpush
    @endif
</x-layout>
