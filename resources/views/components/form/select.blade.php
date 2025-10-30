@props([
    'label' => '',
    'name' => '',
    'value' => '',
    'placeholder' => 'Pilih...',
    'required' => false,
    'helpText' => '',
    'error' => '',
    'options' => [],
    'linkedFieldGroup' => null
])

<div class="mb-4">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1.5">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    <select
        id="{{ $name }}"
        name="{{ $name }}"
        {{ $required ? 'required' : '' }}
        @if($linkedFieldGroup)
            data-linked-group="{{ $linkedFieldGroup }}"
            x-on:change="handleLinkedFieldChange($event)"
            style="border-left: 3px solid #10b981;"
        @endif
        {{ $attributes->merge(['class' => 'w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 bg-white']) }}
    >
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $option)
            <option 
                value="{{ $option['value'] ?? $option }}" 
                {{ old($name, $value) == ($option['value'] ?? $option) ? 'selected' : '' }}
            >
                {{ $option['label'] ?? $option }}
            </option>
        @endforeach
    </select>
    
    @if($helpText)
        <p class="mt-1 text-xs sm:text-sm text-gray-500">{{ $helpText }}</p>
    @endif

    @if($linkedFieldGroup)
        {{-- <p class="mt-1 text-xs text-green-600 flex items-center gap-1">
            <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
            </svg>
            <span>Terhubung dengan field pilihan lainnya</span>
        </p> --}}
    @endif

    @if($error)
        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $error }}</p>
    @endif
</div>
