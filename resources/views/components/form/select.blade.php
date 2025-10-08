@props([
    'label' => '',
    'name' => '',
    'value' => '',
    'placeholder' => 'Pilih...',
    'required' => false,
    'helpText' => '',
    'error' => '',
    'options' => []
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
    
    @if($error)
        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $error }}</p>
    @endif
</div>
