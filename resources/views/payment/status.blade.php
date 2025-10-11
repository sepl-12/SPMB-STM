<x-layout>
    <x-slot name="title">Status Pembayaran - PPDB SMK</x-slot>

    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-green-50 py-6 sm:py-12">
        <div class="max-w-3xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8">
            
            <!-- Status Card -->
            <div class="bg-white rounded-xl sm:rounded-2xl shadow-xl p-6 sm:p-8">
                
                @if($latestPayment)
                    @php
                        $isPaid = $latestPayment->payment_status_name->isSuccess();
                        $isPending = $latestPayment->payment_status_name->isPending();
                        $isFailed = $latestPayment->payment_status_name->isFailed();
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
                                <span class="font-semibold {{ $isPaid ? 'text-green-600' : ($isPending ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ $isPaid ? 'Lunas' : ($isPending ? 'Menunggu' : 'Gagal') }}
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
                                href="{{ route('payment.show', $applicant->registration_number) }}"
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
                            href="{{ route('payment.show', $applicant->registration_number) }}"
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

    @if($latestPayment && $latestPayment->payment_status_name->isPending())
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

        // Auto-check status every 30 seconds
        setInterval(checkPaymentStatus, 30000);
    </script>
    @endpush
    @endif
</x-layout>
