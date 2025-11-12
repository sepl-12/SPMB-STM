<x-layout>
    <x-slot name="title">Pembayaran Manual - PPDB SMK</x-slot>

    <div class="min-h-screen bg-gradient-to-br from-orange-50 via-white to-yellow-50 py-6 sm:py-12">
        <div class="max-w-4xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8">

            <!-- Emergency Alert -->
            <div class="bg-orange-100 border-l-4 border-orange-500 p-4 mb-6 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-orange-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-orange-800">
                            Mode Pembayaran Darurat Aktif
                        </h3>
                        <div class="mt-2 text-sm text-orange-700">
                            <p>Sistem pembayaran otomatis sedang dalam perbaikan. Silakan lakukan pembayaran manual melalui QRIS dan upload bukti pembayaran.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Card -->
            <div class="bg-white rounded-xl sm:rounded-2xl shadow-xl p-6 sm:p-8">

                <!-- Header -->
                <div class="text-center mb-6 sm:mb-8">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 sm:h-16 sm:w-16 rounded-full bg-orange-100 mb-4">
                        <svg class="h-8 w-8 sm:h-10 sm:w-10 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">
                        Pembayaran Manual (QRIS)
                    </h1>
                    <p class="text-sm sm:text-base text-gray-600">
                        Scan QRIS dan upload bukti pembayaran Anda
                    </p>
                </div>

                <!-- Applicant Info -->
                <div class="bg-gradient-to-r from-orange-50 to-yellow-50 rounded-xl p-4 sm:p-6 mb-6">
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
                            <p class="text-gray-600">Jumlah yang Harus Dibayar</p>
                            <p class="font-bold text-green-600 text-lg">Rp {{ number_format($registrationFee, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                <!-- QRIS Section -->
                @if($qrisImage)
                <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 mb-6 bg-gray-50">
                    <h2 class="font-semibold text-gray-900 mb-4 text-center">Scan QRIS Ini</h2>
                    <div class="flex flex-col items-center">
                        <img src="{{ asset('storage/' . $qrisImage) }}"
                             alt="QRIS Payment"
                             class="max-w-xs w-full h-auto rounded-lg shadow-md mb-4">
                        <p class="text-sm text-gray-600 text-center">{{ $accountName }}</p>
                        <p class="text-xs text-gray-500 text-center mt-1">
                            Scan dengan: GoPay, OVO, DANA, Mobile Banking, atau app pembayaran lainnya
                        </p>
                    </div>
                </div>
                @else
                <div class="border-2 border-dashed border-red-300 rounded-xl p-6 mb-6 bg-red-50">
                    <p class="text-red-600 text-center">⚠️ QRIS belum diupload oleh admin. Hubungi panitia PPDB.</p>
                </div>
                @endif

                <!-- Instructions -->
                @if($instructions)
                <div class="bg-blue-50 rounded-xl p-4 sm:p-6 mb-6">
                    <h2 class="font-semibold text-gray-900 mb-3 text-sm sm:text-base">Cara Pembayaran</h2>
                    <div class="text-sm text-gray-700 whitespace-pre-line">{{ $instructions }}</div>
                </div>
                @endif

                <!-- Upload Form -->
                <form action="{{ route('payment.upload-manual', $applicant->registration_number) }}"
                      method="POST"
                      enctype="multipart/form-data"
                      class="space-y-6">
                    @csrf

                    <!-- Proof Image Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Bukti Pembayaran <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-orange-400 transition-colors">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="proof_image" class="relative cursor-pointer bg-white rounded-md font-medium text-orange-600 hover:text-orange-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-orange-500">
                                        <span>Upload file</span>
                                        <input id="proof_image"
                                               name="proof_image"
                                               type="file"
                                               class="sr-only"
                                               accept="image/jpeg,image/jpg,image/png"
                                               required
                                               onchange="previewImage(event)">
                                    </label>
                                    <p class="pl-1">atau drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG maksimal 2MB</p>
                            </div>
                        </div>
                        <!-- Image Preview -->
                        <div id="imagePreview" class="mt-4 hidden">
                            <img id="preview" class="max-w-xs mx-auto rounded-lg shadow-md" alt="Preview">
                        </div>
                        @error('proof_image')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Paid Amount -->
                    <div class="mb-4">
                        <label for="paid_amount" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Jumlah yang Dibayar <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 sm:pl-4 flex items-center pointer-events-none">
                                <span class="text-gray-500 text-sm sm:text-base">Rp</span>
                            </div>
                            <input type="number"
                                   name="paid_amount"
                                   id="paid_amount"
                                   value="{{ old('paid_amount', $registrationFee) }}"
                                   class="w-full pl-12 sm:pl-14 pr-3 sm:pr-4 py-2.5 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 @error('paid_amount') border-red-300 @enderror"
                                   placeholder="{{ $registrationFee }}"
                                   readonly
                                   required>
                        </div>
                        <p class="mt-1 text-xs sm:text-sm text-gray-500">Jumlah sesuai biaya pendaftaran</p>
                        @error('paid_amount')
                            <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Payment Notes -->
                    <div class="mb-4">
                        <label for="payment_notes" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Catatan (Opsional)
                        </label>
                        <textarea name="payment_notes"
                                  id="payment_notes"
                                  rows="3"
                                  class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 resize-y @error('payment_notes') border-red-300 @enderror"
                                  placeholder="Contoh: Bayar dari rekening BCA a.n. John Doe">{{ old('payment_notes') }}</textarea>
                        <p class="mt-1 text-xs sm:text-sm text-gray-500">Opsional: Tambahkan informasi tambahan jika diperlukan</p>
                        @error('payment_notes')
                            <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex flex-col sm:flex-row gap-3 mt-2">
                        <button type="submit"
                                class="w-full px-8 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg font-medium hover:from-green-700 hover:to-green-800 transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            Upload Bukti Pembayaran
                        </button>
                    </div>
                </form>

                <!-- Security Notice -->
                <div class="mt-6 flex items-start text-xs text-gray-500">
                    <svg class="flex-shrink-0 h-5 w-5 text-gray-400 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <span>
                        Bukti pembayaran Anda akan diverifikasi oleh admin dalam waktu maksimal 1x24 jam. Anda akan menerima notifikasi melalui email setelah pembayaran diverifikasi.
                    </span>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('preview');
                    const previewContainer = document.getElementById('imagePreview');
                    preview.src = e.target.result;
                    previewContainer.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        }
    </script>
    @endpush
</x-layout>
