<x-layout>
    <x-slot name="title">Cek Status Pembayaran - PPDB SMK</x-slot>

    <div class="min-h-screen bg-gradient-to-br from-green-50 via-white to-blue-50 py-6 sm:py-12">
        <div class="max-w-2xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="text-center mb-6 sm:mb-8">
                <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mb-2 px-2">
                    Cek Status Pembayaran
                </h1>
                <p class="text-sm sm:text-base text-gray-600 px-2">
                    Masukkan nomor pendaftaran dan email untuk mengecek status pembayaran Anda.
                </p>
            </div>
            
            <!-- Check Payment Card -->
            <div class="bg-white rounded-xl sm:rounded-2xl shadow-xl p-4 sm:p-6 md:p-8">

                <!-- Alert Messages -->
                @if(session('error'))
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4 flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm text-red-800">{{ session('error') }}</p>
                    </div>
                @endif

                @if(session('success'))
                    <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4 flex items-start gap-3">
                        <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm text-green-800">{{ session('success') }}</p>
                    </div>
                @endif

                @if(session('info'))
                    <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm text-blue-800">{{ session('info') }}</p>
                    </div>
                @endif

                <!-- Info Box -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
                    <div class="flex gap-2 sm:gap-3">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-2 text-sm sm:text-base">Informasi Penting</h3>
                            <ul class="list-disc list-inside space-y-1.5 sm:space-y-2 text-xs sm:text-sm text-gray-700">
                                <li>Nomor pendaftaran dikirim ke email setelah Anda mendaftar</li>
                                <li>Format: <code class="bg-yellow-100 px-1 rounded font-mono">PPDB-2024-00001</code></li>
                                <li>Gunakan email yang sama saat mendaftar</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form action="{{ route('payment.find') }}" method="POST" id="checkForm">
                    @csrf
                    
                    <div class="space-y-4">
                        <!-- Registration Number Field -->
                        <div>
                            <label for="registration_number" class="block text-sm font-medium text-gray-700 mb-2">
                                Nomor Pendaftaran <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-colors duration-200 @error('registration_number') border-red-500 @enderror" 
                                   id="registration_number" 
                                   name="registration_number" 
                                   placeholder="Contoh: PPDB-2024-00001"
                                   value="{{ old('registration_number') }}"
                                   required>
                            @error('registration_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email Field -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-colors duration-200 @error('email') border-red-500 @enderror" 
                                   id="email" 
                                   name="email" 
                                   placeholder="email@example.com"
                                   value="{{ old('email') }}"
                                   required>
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="mt-6 w-full inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg font-medium hover:from-green-700 hover:to-green-800 transition-all duration-200 shadow-lg hover:shadow-xl text-sm sm:text-base">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Cek Status Pembayaran
                    </button>
                </form>

                <!-- Divider -->
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">atau</span>
                    </div>
                </div>

                <!-- Resend Link Button -->
                <button type="button" 
                        id="resendLinkBtn"
                        class="w-full inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors duration-200 text-sm sm:text-base">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Kirim Ulang Link Pembayaran ke Email
                </button>

                <!-- Back Link -->
                <div class="text-center mt-6">
                    <a href="{{ route('home') }}" class="text-xs sm:text-sm text-gray-600 hover:text-gray-900 underline">
                        Kembali ke Halaman Utama
                    </a>
                </div>
            </div>

            <!-- Help Card -->
            <div class="bg-white rounded-xl shadow-xl p-4 sm:p-6 mt-6">
                <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2 text-sm sm:text-base">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Butuh Bantuan?
                </h3>
                <div class="space-y-2 text-xs sm:text-sm text-gray-600">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        <span>Telp: <a href="tel:+6281234567890" class="text-green-600 font-medium hover:underline">+62 812-3456-7890</a></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        <span>WhatsApp: <a href="https://wa.me/6281234567890" class="text-green-600 font-medium hover:underline" target="_blank">+62 812-3456-7890</a></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span>Email: <a href="mailto:ppdb@stm.ac.id" class="text-green-600 font-medium hover:underline">ppdb@stm.ac.id</a></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Resend Link Handler
        document.getElementById('resendLinkBtn').addEventListener('click', async function() {
            const btn = this;
            const regNum = document.getElementById('registration_number').value;
            const email = document.getElementById('email').value;

            if (!regNum || !email) {
                alert('Mohon isi nomor pendaftaran dan email terlebih dahulu');
                return;
            }

            // Disable button
            btn.disabled = true;
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<span class="inline-block animate-spin mr-2">⏳</span>Mengirim...';

            try {
                const response = await fetch('{{ route('payment.resend-link') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        registration_number: regNum,
                        email: email
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alert('✅ ' + data.message);
                    if (data.payment_url) {
                        if (confirm('Link pembayaran tersedia. Buka sekarang?')) {
                            window.location.href = data.payment_url;
                        }
                    }
                } else {
                    alert('❌ ' + (data.message || 'Gagal mengirim link'));
                }
            } catch (error) {
                alert('❌ Terjadi kesalahan. Silakan coba lagi.');
                console.error('Error:', error);
            } finally {
                // Enable button
                btn.disabled = false;
                btn.innerHTML = originalContent;
            }
        });

        // Auto uppercase registration number
        document.getElementById('registration_number').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });

        // Form validation
        document.getElementById('checkForm').addEventListener('submit', function(e) {
            const regNum = document.getElementById('registration_number').value;
            const email = document.getElementById('email').value;

            if (!regNum || !email) {
                e.preventDefault();
                alert('Mohon isi semua field');
            }
        });
    </script>
    @endpush
</x-layout>
