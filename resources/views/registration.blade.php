@php
    // Fetch active form version with steps and fields
    $form = \App\Models\Form::with([
        'activeFormVersion.formSteps' => function($query) {
            $query->where('is_visible_for_public', true)
                  ->orderBy('step_order_number');
        },
        'activeFormVersion.formSteps.formFields' => function($query) {
            $query->where('is_archived', false)
                  ->orderBy('field_order_number');
        }
    ])->first();

    $formVersion = $form?->activeFormVersion;
    $steps = $formVersion?->formSteps ?? collect();
    
    // Get current step from session or default to first step
    $currentStepIndex = session('current_step', 0);
    $currentStepIndex = min($currentStepIndex, count($steps) - 1);
    $currentStep = $steps[$currentStepIndex] ?? null;
    
    // Get form data from session
    $formData = session('registration_data', []);
@endphp

<x-layout>
    <x-slot name="title">Formulir Pendaftaran Siswa Baru - PPDB SMK</x-slot>

    <div class="min-h-screen bg-gradient-to-br from-green-50 via-white to-blue-50 py-6 sm:py-12">
        <div class="max-w-4xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="text-center mb-6 sm:mb-8">
                <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mb-2 px-2">
                    Formulir Pendaftaran Siswa Baru
                </h1>
                <p class="text-sm sm:text-base text-gray-600 px-2">
                    Silakan lengkapi data berikut dengan benar dan teliti.
                </p>
            </div>

            <!-- Alert Messages -->
            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4 flex items-start gap-3">
                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4 flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-red-800 mb-2">Terdapat kesalahan pada form:</p>
                            <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Wizard Progress -->
            @if($steps->count() > 0)
                <x-wizard-progress 
                    :steps="$steps->map(fn($s) => ['title' => $s->step_title])->toArray()" 
                    :currentStep="$currentStepIndex + 1" 
                />
            @endif

            <!-- Main Form Card -->
            <div class="bg-white rounded-xl sm:rounded-2xl shadow-xl p-4 sm:p-6 md:p-8">
                @if($currentStep)
                    <!-- Step Header -->
                    <div class="mb-4 sm:mb-6">
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">
                            Langkah {{ $currentStepIndex + 1 }}: {{ $currentStep->step_title }}
                        </h2>
                        @if($currentStep->step_description)
                            <p class="text-sm sm:text-base text-gray-600">{{ $currentStep->step_description }}</p>
                        @endif
                    </div>

                    <!-- Form -->
                    <form method="POST" action="{{ route('registration.save-step') }}" enctype="multipart/form-data" x-data="registrationForm()">
                        @csrf
                        <input type="hidden" name="current_step" value="{{ $currentStepIndex }}">
                        
                        <!-- Dynamic Fields -->
                        <div class="space-y-4">
                            @foreach($currentStep->formFields as $field)
                                @php
                                    $fieldValue = $formData[$field->field_key] ?? '';
                                    $fieldOptions = $field->field_options_json ?? [];
                                @endphp

                                @switch($field->field_type)
                                    @case('text')
                                    @case('email')
                                    @case('tel')
                                        <x-form.text-input
                                            :label="$field->field_label"
                                            :name="$field->field_key"
                                            :value="$fieldValue"
                                            :placeholder="$field->field_placeholder_text"
                                            :required="$field->is_required"
                                            :helpText="$field->field_help_text"
                                            :error="$errors->first($field->field_key)"
                                        />
                                        @break

                                    @case('textarea')
                                        <x-form.textarea
                                            :label="$field->field_label"
                                            :name="$field->field_key"
                                            :value="$fieldValue"
                                            :placeholder="$field->field_placeholder_text"
                                            :required="$field->is_required"
                                            :helpText="$field->field_help_text"
                                            :error="$errors->first($field->field_key)"
                                        />
                                        @break

                                    @case('number')
                                        <x-form.number-input
                                            :label="$field->field_label"
                                            :name="$field->field_key"
                                            :value="$fieldValue"
                                            :placeholder="$field->field_placeholder_text"
                                            :required="$field->is_required"
                                            :helpText="$field->field_help_text"
                                            :error="$errors->first($field->field_key)"
                                        />
                                        @break

                                    @case('date')
                                        <x-form.date-input
                                            :label="$field->field_label"
                                            :name="$field->field_key"
                                            :value="$fieldValue"
                                            :placeholder="$field->field_placeholder_text"
                                            :required="$field->is_required"
                                            :helpText="$field->field_help_text"
                                            :error="$errors->first($field->field_key)"
                                        />
                                        @break

                                    @case('select')
                                        <x-form.select
                                            :label="$field->field_label"
                                            :name="$field->field_key"
                                            :value="$fieldValue"
                                            :placeholder="$field->field_placeholder_text"
                                            :required="$field->is_required"
                                            :helpText="$field->field_help_text"
                                            :error="$errors->first($field->field_key)"
                                            :options="$fieldOptions"
                                        />
                                        @break

                                    @case('multiselect')
                                        <x-form.multi-select
                                            :label="$field->field_label"
                                            :name="$field->field_key"
                                            :value="$fieldValue"
                                            :required="$field->is_required"
                                            :helpText="$field->field_help_text"
                                            :error="$errors->first($field->field_key)"
                                            :options="$fieldOptions"
                                        />
                                        @break

                                    @case('radio')
                                        <x-form.radio
                                            :label="$field->field_label"
                                            :name="$field->field_key"
                                            :value="$fieldValue"
                                            :required="$field->is_required"
                                            :helpText="$field->field_help_text"
                                            :error="$errors->first($field->field_key)"
                                            :options="$fieldOptions"
                                        />
                                        @break

                                    @case('file')
                                    @case('image')
                                        <x-form.file-upload
                                            :label="$field->field_label"
                                            :name="$field->field_key"
                                            :value="$fieldValue"
                                            :required="$field->is_required"
                                            :helpText="$field->field_help_text"
                                            :error="$errors->first($field->field_key)"
                                            :accept="$field->field_type === 'image' ? 'image/*' : ''"
                                        />
                                        @break

                                    @case('boolean')
                                    @case('checkbox')
                                        <x-form.checkbox
                                            :label="$field->field_label"
                                            :name="$field->field_key"
                                            :checked="$fieldValue"
                                            :required="$field->is_required"
                                            :helpText="$field->field_help_text"
                                            :error="$errors->first($field->field_key)"
                                        />
                                        @break
                                @endswitch
                            @endforeach
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="mt-6 sm:mt-8 flex flex-col-reverse sm:flex-row justify-between items-stretch sm:items-center gap-3">
                            <!-- Previous Button -->
                            @if($currentStepIndex > 0)
                                <button
                                    type="submit"
                                    name="action"
                                    value="previous"
                                    formnovalidate
                                    class="w-full sm:w-auto px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors duration-200 text-center"
                                >
                                    ← Sebelumnya
                                </button>
                            @else
                                <div class="hidden sm:block"></div>
                            @endif

                            <!-- Next/Submit Button -->
                            <button
                                type="submit"
                                name="action"
                                value="{{ $currentStepIndex < $steps->count() - 1 ? 'next' : 'submit' }}"
                                {{ $currentStepIndex < $steps->count() - 1 ? 'formnovalidate' : '' }}
                                class="w-full sm:w-auto px-8 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg font-medium hover:from-green-700 hover:to-green-800 transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center gap-2"
                            >
                                @if($currentStepIndex < $steps->count() - 1)
                                    Selanjutnya →
                                @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Kirim Formulir
                                @endif
                            </button>
                        </div>
                    </form>

                    <!-- Step Navigator (Optional - Quick Jump) -->
                    <div class="mt-6 sm:mt-8 pt-6 border-t border-gray-200">
                        <p class="text-sm text-gray-600 mb-3">Navigasi Cepat:</p>
                        <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2">
                            @foreach($steps as $index => $step)
                                <button
                                    type="button"
                                    onclick="quickJump({{ $index }})"
                                    class="px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-medium transition-colors duration-200 {{ $index == $currentStepIndex ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                                >
                                    <span class="hidden sm:inline">{{ $step->step_title }}</span>
                                    <span class="sm:hidden">{{ $index + 1 }}. {{ Str::limit($step->step_title, 15) }}</span>
                                </button>
                            @endforeach
                        </div>
                        
                        <!-- Hidden form for quick jump -->
                        <form id="quickJumpForm" method="POST" action="{{ route('registration.jump-to-step') }}" style="display: none;">
                            @csrf
                            <input type="hidden" name="jump_to_step" id="jumpToStepInput" value="0">
                        </form>
                    </div>
                @else
                    <!-- No Form Available -->
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-lg font-medium text-gray-900">Formulir Belum Tersedia</h3>
                        <p class="mt-1 text-gray-500">Silakan hubungi administrator untuk informasi lebih lanjut.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function registrationForm() {
            return {
                init() {
                    // Auto-save to localStorage (optional)
                    this.$watch('$el', (value) => {
                        // Optional: implement auto-save functionality
                    });
                }
            }
        }
        
        // Quick jump function - bypasses form validation
        function quickJump(stepIndex) {
            document.getElementById('jumpToStepInput').value = stepIndex;
            document.getElementById('quickJumpForm').submit();
        }
        
        // Save current form data before navigation (optional enhancement)
        function saveCurrentStepData() {
            // This function can be called before quick jump to save current data
            // Currently handled by server-side on form submit
        }
    </script>
    @endpush
</x-layout>
