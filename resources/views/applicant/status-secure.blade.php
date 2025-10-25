<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pendaftaran - {{ $applicant->registration_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-8 px-4">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Status Pendaftaran</h1>
                <p class="text-gray-600">Informasi lengkap pendaftaran dan pembayaran Anda</p>
            </div>

            <!-- Registration Info Card -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white p-6">
                    <h2 class="text-xl font-semibold mb-2">Informasi Pendaftaran</h2>
                    <p class="text-blue-100">{{ $wave->name ?? 'SPMB STM' }}</p>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-sm text-gray-600 font-medium">Nomor Pendaftaran</label>
                            <p class="text-xl font-bold text-gray-900">{{ $applicant->registration_number }}</p>
                        </div>
                        
                        <div>
                            <label class="text-sm text-gray-600 font-medium">Tanggal Pendaftaran</label>
                            <p class="text-gray-900 font-semibold">
                                {{ $applicant->registered_datetime ? $applicant->registered_datetime->isoFormat('D MMMM YYYY, HH:mm') : '-' }}
                            </p>
                        </div>

                        <div>
                            <label class="text-sm text-gray-600 font-medium">Nama Lengkap</label>
                            <p class="text-gray-900 font-semibold">{{ $applicant->name ?? '-' }}</p>
                        </div>

                        @php
                            $latestAnswers = $applicant->getLatestSubmissionAnswers();
                        @endphp

                        @if(!empty($latestAnswers['email']))
                        <div>
                            <label class="text-sm text-gray-600 font-medium">Email</label>
                            <p class="text-gray-900">{{ $latestAnswers['email'] }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Payment Status Card -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Status Pembayaran</h2>
                </div>
                
                <div class="p-6">
                    @if($payment)
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                @php
                                    $statusBadge = $applicant->payment_status_badge;
                                    $colorClasses = match($statusBadge['color']) {
                                        'success' => 'bg-green-100 text-green-800',
                                        'warning' => 'bg-yellow-100 text-yellow-800',
                                        'danger' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                @endphp
                                <span class="px-4 py-2 rounded-full text-sm font-semibold {{ $colorClasses }}">
                                    {{ $statusBadge['label'] }}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="text-sm text-gray-600 font-medium">Kode Pembayaran</label>
                                <p class="text-gray-900 font-mono">{{ $payment->merchant_order_code ?? '-' }}</p>
                            </div>

                            <div>
                                <label class="text-sm text-gray-600 font-medium">Jumlah</label>
                                <p class="text-gray-900 font-semibold">
                                    Rp {{ number_format($payment->amount ?? 0, 0, ',', '.') }}
                                </p>
                            </div>

                            @if($payment->payment_type)
                            <div>
                                <label class="text-sm text-gray-600 font-medium">Metode Pembayaran</label>
                                <p class="text-gray-900">{{ ucwords(str_replace('_', ' ', $payment->payment_type)) }}</p>
                            </div>
                            @endif

                            @if($payment->status_updated_datetime)
                            <div>
                                <label class="text-sm text-gray-600 font-medium">Terakhir Diperbarui</label>
                                <p class="text-gray-900">
                                    {{ \Carbon\Carbon::parse($payment->status_updated_datetime)->isoFormat('D MMMM YYYY, HH:mm') }}
                                </p>
                            </div>
                            @endif
                        </div>

                        @if($applicant->hasSuccessfulPayment())
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <div class="flex items-start gap-3">
                                    <svg class="w-6 h-6 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <p class="font-semibold text-green-900 mb-1">Pembayaran Berhasil!</p>
                                        <p class="text-sm text-green-800">
                                            Terima kasih. Pembayaran Anda telah dikonfirmasi. Silakan download kartu ujian Anda.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @elseif($applicant->hasPendingPayment())
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <div class="flex items-start gap-3">
                                    <svg class="w-6 h-6 text-yellow-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <p class="font-semibold text-yellow-900 mb-1">Pembayaran Menunggu Konfirmasi</p>
                                        <p class="text-sm text-yellow-800">
                                            Pembayaran Anda sedang diproses. Harap tunggu konfirmasi dari sistem pembayaran.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <p class="text-gray-600 mb-4">Belum ada data pembayaran</p>
                            <a href="{{ route('payment.show', $applicant->registration_number) }}" 
                               class="inline-block bg-blue-600 text-white font-semibold py-2 px-6 rounded-lg hover:bg-blue-700 transition duration-200">
                                Lakukan Pembayaran
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($applicant->hasSuccessfulPayment())
                    <a href="{{ $applicant->exam_card_url }}" 
                       class="flex items-center justify-center gap-2 bg-green-600 text-white font-semibold py-3 px-6 rounded-lg hover:bg-green-700 transition duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                        </svg>
                        Download Kartu Ujian
                    </a>
                    @else
                    <a href="{{ $applicant->payment_url }}" 
                       class="flex items-center justify-center gap-2 bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg hover:bg-blue-700 transition duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        Lanjutkan Pembayaran
                    </a>
                    @endif

                    <a href="{{ route('home') }}" 
                       class="flex items-center justify-center gap-2 bg-white text-gray-700 font-semibold py-3 px-6 rounded-lg border border-gray-300 hover:bg-gray-50 transition duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Kembali ke Beranda
                    </a>
                </div>
            </div>

            <!-- Help Section -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Butuh bantuan? Hubungi kami di 
                    <a href="mailto:info@stm.ac.id" class="text-blue-600 hover:text-blue-800 font-semibold">info@stm.ac.id</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
