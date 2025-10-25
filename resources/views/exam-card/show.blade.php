<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Ujian - {{ $applicant->registration_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none; }
            body { background: white; }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-8 px-4">
        <div class="max-w-4xl mx-auto">
            <!-- Header Actions -->
            <div class="no-print mb-6 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">Kartu Ujian</h1>
                <button onclick="window.print()" 
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-200 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Cetak Kartu
                </button>
            </div>

            <!-- Exam Card -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white p-6 text-center">
                    <h2 class="text-2xl font-bold mb-2">KARTU PESERTA UJIAN</h2>
                    <p class="text-blue-100">{{ $wave->name ?? 'SPMB STM' }}</p>
                    @if($wave && $wave->exam_date)
                    <p class="text-blue-100 mt-1">{{ \Carbon\Carbon::parse($wave->exam_date)->isoFormat('dddd, D MMMM YYYY') }}</p>
                    @endif
                </div>

                <!-- Content -->
                <div class="p-8">
                    <!-- QR Code Section -->
                    <div class="flex flex-col md:flex-row gap-8 mb-8">
                        <div class="flex-shrink-0 mx-auto md:mx-0">
                            <div class="w-40 h-40 bg-gray-100 border-2 border-gray-300 rounded-lg flex items-center justify-center">
                                <!-- QR Code placeholder - implement with actual QR library -->
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ $applicant->registration_number }}" 
                                     alt="QR Code" 
                                     class="w-full h-full object-contain p-2">
                            </div>
                        </div>

                        <!-- Applicant Info -->
                        <div class="flex-1">
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="text-sm text-gray-600 font-medium">Nomor Pendaftaran</label>
                                    <p class="text-xl font-bold text-gray-900">{{ $applicant->registration_number }}</p>
                                </div>
                                
                                <div>
                                    <label class="text-sm text-gray-600 font-medium">Nama Lengkap</label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $applicant->name ?? '-' }}</p>
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

                                @if(!empty($latestAnswers['phone']))
                                <div>
                                    <label class="text-sm text-gray-600 font-medium">Nomor Telepon</label>
                                    <p class="text-gray-900">{{ $latestAnswers['phone'] }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Exam Details -->
                    @if($wave)
                    <div class="border-t border-gray-200 pt-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Detail Ujian</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if($wave->exam_date)
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="text-sm text-gray-600 font-medium">Tanggal Ujian</label>
                                <p class="text-gray-900 font-semibold">{{ \Carbon\Carbon::parse($wave->exam_date)->isoFormat('dddd, D MMMM YYYY') }}</p>
                            </div>
                            @endif

                            @if($wave->exam_time)
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="text-sm text-gray-600 font-medium">Waktu Ujian</label>
                                <p class="text-gray-900 font-semibold">{{ $wave->exam_time }}</p>
                            </div>
                            @endif

                            @if($wave->exam_location)
                            <div class="bg-gray-50 p-4 rounded-lg md:col-span-2">
                                <label class="text-sm text-gray-600 font-medium">Lokasi Ujian</label>
                                <p class="text-gray-900 font-semibold">{{ $wave->exam_location }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Instructions -->
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Petunjuk Peserta</h3>
                        <ol class="list-decimal list-inside space-y-2 text-gray-700">
                            <li>Harap membawa kartu ujian ini pada saat pelaksanaan ujian</li>
                            <li>Datang 30 menit sebelum ujian dimulai</li>
                            <li>Membawa alat tulis (pensil 2B, penghapus, ballpoint)</li>
                            <li>Membawa kartu identitas asli (KTP/SIM/Kartu Pelajar)</li>
                            <li>Tidak diperkenankan membawa HP dan perangkat elektronik lainnya ke ruang ujian</li>
                            <li>Berpakaian rapi dan sopan</li>
                        </ol>
                    </div>

                    <!-- Footer -->
                    <div class="border-t border-gray-200 mt-8 pt-6 text-center">
                        <p class="text-sm text-gray-600">
                            Kartu ini dicetak pada {{ now()->isoFormat('dddd, D MMMM YYYY HH:mm') }}
                        </p>
                        <p class="text-xs text-gray-500 mt-2">
                            Untuk informasi lebih lanjut, hubungi panitia SPMB STM
                        </p>
                    </div>
                </div>
            </div>

            <!-- Back Button -->
            <div class="no-print mt-6 text-center">
                <a href="{{ route('home') }}" 
                   class="inline-block bg-white text-gray-700 font-semibold py-3 px-6 rounded-lg border border-gray-300 hover:bg-gray-50 transition duration-200">
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</body>
</html>
