@props([
    'label' => '',
    'name' => '',
    'value' => '',
    'required' => false,
    'helpText' => '',
    'error' => '',
    'options' => []
])

<div class="mb-4">
    @if($label)
        <label class="block text-sm font-medium text-gray-700 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    <div class="space-y-2">
        @foreach($options as $option)
            @php
                $optionValue = $option['value'] ?? $option;
                $optionLabel = $option['label'] ?? $option;
            @endphp
            <label class="flex items-center hover:bg-gray-50 p-2 rounded cursor-pointer transition-colors">
                <input
                    type="radio"
                    name="{{ $name }}"
                    value="{{ $optionValue }}"
                    {{ old($name, $value) == $optionValue ? 'checked' : '' }}
                    {{ $required ? 'required' : '' }}
                    class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500 focus:ring-2"
                />
                <span class="ml-2 text-sm text-gray-700">{{ $optionLabel }}</span>
            </label>
        @endforeach
    </div>
    
    @if($helpText)
        <p class="mt-1 text-sm text-gray-500">{{ $helpText }}</p>
    @endif
    
    @if($error)
        <p class="mt-1 text-sm text-red-600">{{ $error }}</p>
    @endif
</div>
