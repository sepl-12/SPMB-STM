@props([
    'label' => '',
    'name' => '',
    'value' => [],
    'placeholder' => 'Pilih...',
    'required' => false,
    'helpText' => '',
    'error' => '',
    'options' => []
])

@php
    $selectedValues = old($name, is_array($value) ? $value : []);
@endphp

<div class="mb-4">
    @if($label)
        <label class="block text-sm font-medium text-gray-700 mb-1.5">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    <div class="border border-gray-300 rounded-lg p-2 sm:p-3 space-y-1.5 sm:space-y-2 max-h-48 overflow-y-auto bg-white">
        @foreach($options as $option)
            @php
                $optionValue = $option['value'] ?? $option;
                $optionLabel = $option['label'] ?? $option;
            @endphp
            <label class="flex items-center hover:bg-gray-50 p-2 rounded cursor-pointer transition-colors">
                <input
                    type="checkbox"
                    name="{{ $name }}[]"
                    value="{{ $optionValue }}"
                    {{ in_array($optionValue, $selectedValues) ? 'checked' : '' }}
                    class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500 focus:ring-2 flex-shrink-0"
                />
                <span class="ml-2 text-xs sm:text-sm text-gray-700">{{ $optionLabel }}</span>
            </label>
        @endforeach
    </div>
    
    @if($helpText)
        <p class="mt-1 text-xs sm:text-sm text-gray-500">{{ $helpText }}</p>
    @endif
    
    @if($error)
        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $error }}</p>
    @endif
</div>
