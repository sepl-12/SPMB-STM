<x-layout>
    <x-slot name="title">Preview Data Pendaftaran - PPDB SMK</x-slot>

    <div class="min-h-screen bg-gray-50 py-6 sm:py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-2xl sm:text-3xl font-medium text-gray-900 mb-2">
                    Preview Data Pendaftaran
                </h1>
                <p class="text-sm text-gray-600">
                    Periksa kembali data Anda sebelum mengirimkan formulir pendaftaran.
                </p>
            </div>

            <!-- Preview Sections -->
            <div class="space-y-6 mb-6">
                @if(count($previewData) > 0)
                    @foreach($previewData as $stepIndex => $stepData)
                        <!-- Individual Step Card -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                            <!-- Step Header -->
                            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center justify-center w-8 h-8 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
                                            {{ $stepData['step_order'] }}
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h2 class="text-lg font-semibold text-gray-900">
                                            {{ $stepData['step_title'] }}
                                        </h2>
                                        @if(!empty($stepData['step_description']))
                                            <p class="text-sm text-gray-600 mt-1">{{ $stepData['step_description'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Step Content -->
                            <div class="p-6">
                                <div class="space-y-5">
                                    @foreach($stepData['fields'] as $field)
                                        <div class="border-b border-gray-100 pb-4 last:border-b-0 last:pb-0">
                                            <!-- Question Label -->
                                            <div class="mb-3">
                                                <label class="block text-sm font-medium text-gray-900">
                                                    {{ $field['field_label'] }}
                                                    @if($field['is_required'])
                                                        <span class="text-red-500 ml-1">*</span>
                                                    @endif
                                                </label>
                                            </div>

                                            <!-- Answer -->
                                            <div class="text-sm text-gray-700 ml-0">
                                                @if($field['field_type'] === 'multi_checkbox' && is_array($field['value']))
                                                    <!-- Multi checkbox as bullet list -->
                                                    <div class="bg-gray-50 rounded-md p-3 border border-gray-200">
                                                        <ul class="list-disc list-inside space-y-1">
                                                            @foreach($field['value'] as $selectedValue)
                                                                <li class="text-gray-800">{{ $selectedValue }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @elseif(in_array($field['field_type'], ['image', 'signature', 'checkbox', 'file']))
                                                    <!-- Special field types -->
                                                    <div class="inline-flex items-center px-3 py-2 bg-green-50 text-green-800 rounded-md border border-green-200">
                                                        <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                        <span class="font-medium">
                                                            @switch($field['field_type'])
                                                                @case('image')
                                                                    Gambar telah diunggah
                                                                    @break
                                                                @case('signature')
                                                                    Tanda tangan telah dibuat
                                                                    @break
                                                                @case('file')
                                                                    File telah diunggah
                                                                    @break
                                                                @case('checkbox')
                                                                    Dipilih
                                                                    @break
                                                                @default
                                                                    Terisi
                                                            @endswitch
                                                        </span>
                                                    </div>
                                                @else
                                                    <!-- Regular answer -->
                                                    <div class="bg-gray-50 rounded-md p-3 border border-gray-200">
                                                        <div class="whitespace-pre-wrap text-gray-800">{{ strip_tags($field['formatted_value']) }}</div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach

                @else
                    <!-- No Data -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-lg font-medium text-gray-900">Tidak Ada Data</h3>
                            <p class="mt-1 text-gray-500">Data formulir tidak ditemukan atau sudah tidak valid.</p>
                            <div class="mt-6">
                                <a
                                    href="{{ route('registration.index') }}"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    Kembali ke Formulir
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            @if(count($previewData) > 0)
                <!-- Action Buttons Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Tindakan Selanjutnya</h3>
                    
                    <div class="flex flex-col sm:flex-row gap-3">
                        <!-- Edit Button -->
                        <form method="POST" action="{{ route('registration.preview.edit') }}" class="flex-1">
                            @csrf
                            <input type="hidden" name="jump_to_step" value="0">
                            <button
                                type="submit"
                                class="w-full px-6 py-3 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200 flex items-center justify-center gap-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit Data
                            </button>
                        </form>

                        <!-- Print Button (Desktop Only) -->
                        <button
                            onclick="window.print()"
                            class="hidden sm:flex flex-1 px-6 py-3 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200 items-center justify-center gap-2 print:hidden"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Cetak Preview
                        </button>

                        <!-- Confirm Button -->
                        <form
                            method="POST"
                            action="{{ route('registration.preview.confirm') }}"
                            class="flex-1"
                            onsubmit="return confirm('Apakah Anda yakin ingin mengirimkan data pendaftaran? Data yang sudah dikirim tidak dapat diubah lagi.');"
                        >
                            @csrf
                            <button
                                type="submit"
                                class="w-full px-6 py-3 bg-green-600 text-white rounded-md text-sm font-medium hover:bg-green-700 transition-colors duration-200 flex items-center justify-center gap-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Konfirmasi & Kirim
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Notice Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
                    <div class="bg-amber-50 border-l-4 border-amber-400 p-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-amber-800 mb-1">Perhatian Penting</h3>
                                <p class="text-sm text-amber-700">
                                    Setelah Anda klik <strong>"Konfirmasi & Kirim"</strong>, data pendaftaran akan disimpan secara permanen dan tidak dapat diubah lagi. Pastikan semua informasi sudah benar.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Back Link -->
            <div class="text-center">
                <a href="{{ route('registration.index') }}" class="text-sm text-blue-600 hover:text-blue-500 underline">
                    ← Kembali ke Formulir
                </a>
            </div>

        </div>
    </div>

    @push('styles')
    <style>
        /* Print Styles */
        @media print {
            /* Hide unnecessary elements */
            .print\:hidden {
                display: none !important;
            }

            /* Remove background colors and gradients */
            body {
                background: white !important;
            }

            /* Improve print layout */
            .bg-gradient-to-br,
            .bg-gradient-to-r {
                background: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Keep important colors for badges and highlights */
            .bg-green-100,
            .bg-green-600,
            .bg-green-700,
            .text-green-600,
            .text-green-800 {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Ensure content fits on page */
            .max-w-4xl {
                max-width: 100% !important;
            }

            /* Remove shadows for cleaner print */
            .shadow-xl,
            .shadow-lg {
                box-shadow: none !important;
                border: 1px solid #e5e7eb;
            }

            /* Page breaks */
            .page-break {
                page-break-before: always;
            }

            /* Optimize spacing */
            .py-6,
            .py-12 {
                padding-top: 1rem !important;
                padding-bottom: 1rem !important;
            }

            /* Ensure images print properly */
            img {
                max-width: 100% !important;
                page-break-inside: avoid;
            }
        }

        /* Responsive image styles */
        .preview-image {
            max-width: 100%;
            height: auto;
            display: block;
        }

        /* Signature display */
        .signature-preview {
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 0.5rem;
            background: white;
            display: inline-block;
        }
    </style>
    @endpush
</x-layout>
