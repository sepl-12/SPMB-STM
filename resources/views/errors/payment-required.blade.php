<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Diperlukan - SPMB STM</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full">
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Pembayaran Diperlukan</h1>
                
                <p class="text-gray-600 mb-6">
                    {{ $message ?? 'Anda perlu menyelesaikan pembayaran terlebih dahulu untuk mengakses halaman ini.' }}
                </p>

                @if(isset($applicant))
                <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
                    <p class="text-sm text-gray-600 mb-1">Nomor Pendaftaran</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $applicant->registration_number }}</p>
                    
                    @if($applicant->name)
                    <p class="text-sm text-gray-600 mt-3 mb-1">Nama</p>
                    <p class="text-gray-900">{{ $applicant->name }}</p>
                    @endif
                </div>

                <a href="{{ route('payment.show', $applicant->registration_number) }}" 
                   class="inline-block w-full bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg hover:bg-blue-700 transition duration-200">
                    Lanjutkan Pembayaran
                </a>
                @else
                <a href="{{ route('home') }}" 
                   class="inline-block w-full bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg hover:bg-blue-700 transition duration-200">
                    Kembali ke Beranda
                </a>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
