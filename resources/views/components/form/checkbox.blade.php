@props([
    'label' => '',
    'name' => '',
    'value' => '1',
    'checked' => false,
    'required' => false,
    'helpText' => '',
    'error' => ''
])

<div class="mb-4" x-data="{ checked: {{ old($name, $checked) ? 'true' : 'false' }} }">
    <label class="group flex items-start gap-3 p-3 sm:p-3.5 rounded-lg cursor-pointer transition-all duration-200 hover:bg-gradient-to-r hover:from-green-50 hover:to-blue-50 hover:shadow-sm">
        <!-- Hidden Native Checkbox (for form submission) -->
        <input
            type="checkbox"
            id="{{ $name }}"
            name="{{ $name }}"
            value="{{ $value }}"
            {{ old($name, $checked) ? 'checked' : '' }}
            {{ $required ? 'required' : '' }}
            class="sr-only peer"
            x-model="checked"
            {{ $attributes }}
        />

        <!-- Custom Checkbox Visual -->
        <div class="relative flex-shrink-0 mt-0.5">
            <!-- Checkbox Box -->
            <div class="w-5 h-5 sm:w-6 sm:h-6 border-2 rounded-md transition-all duration-200 ease-in-out
                        peer-focus:ring-4 peer-focus:ring-green-200
                        group-hover:border-green-400 group-hover:shadow-sm
                        border-gray-300 bg-white flex items-center justify-center"
                 :class="{
                     'bg-gradient-to-br from-green-500 to-green-600 border-green-600 shadow-md': checked,
                     'border-gray-300 bg-white': !checked
                 }">

                <!-- Checkmark Icon (appears when checked) -->
                <svg x-show="checked"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-50"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-50"
                     class="w-3 h-3 sm:w-4 sm:h-4 text-white"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="3"
                     viewBox="0 0 24 24"
                     style="display: none;">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>

            <!-- Ripple Effect (on click) -->
            <div x-show="checked"
                 x-transition.opacity
                 class="absolute inset-0 rounded-md bg-green-400 opacity-20 animate-ping-once pointer-events-none">
            </div>
        </div>

        <!-- Label Text -->
        @if($label)
            <div class="flex-1">
                <span class="text-sm sm:text-base text-gray-800 font-medium group-hover:text-gray-900 transition-colors duration-200 select-none">
                    {{ $label }}
                    @if($required)
                        <span class="text-red-500 ml-0.5">*</span>
                    @endif
                </span>

                @if($helpText)
                    <p class="mt-1 text-xs sm:text-sm text-gray-600 leading-relaxed">{{ $helpText }}</p>
                @endif
            </div>
        @endif
    </label>

    @if($error)
        <div class="ml-9 sm:ml-11 mt-2 flex items-start gap-2">
            <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            <p class="text-xs sm:text-sm text-red-600 font-medium">{{ $error }}</p>
        </div>
    @endif
</div>

<style>
    @keyframes ping-once {
        0% {
            transform: scale(1);
            opacity: 0.2;
        }
        50% {
            transform: scale(1.5);
            opacity: 0.1;
        }
        100% {
            transform: scale(2);
            opacity: 0;
        }
    }

    .animate-ping-once {
        animation: ping-once 0.5s cubic-bezier(0, 0, 0.2, 1);
    }
</style>
