@props([
    'label' => '',
    'name' => '',
    'value' => '1',
    'checked' => false,
    'required' => false,
    'helpText' => '',
    'error' => ''
])

<div class="mb-4">
    <label class="flex items-start hover:bg-gray-50 p-2 rounded cursor-pointer transition-colors">
        <input
            type="checkbox"
            id="{{ $name }}"
            name="{{ $name }}"
            value="{{ $value }}"
            {{ old($name, $checked) ? 'checked' : '' }}
            {{ $required ? 'required' : '' }}
            {{ $attributes->merge(['class' => 'w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500 focus:ring-2 mt-0.5']) }}
        />
        @if($label)
            <span class="ml-2 text-sm text-gray-700">
                {{ $label }}
                @if($required)
                    <span class="text-red-500">*</span>
                @endif
            </span>
        @endif
    </label>
    
    @if($helpText)
        <p class="ml-6 mt-1 text-sm text-gray-500">{{ $helpText }}</p>
    @endif
    
    @if($error)
        <p class="ml-6 mt-1 text-sm text-red-600">{{ $error }}</p>
    @endif
</div>
