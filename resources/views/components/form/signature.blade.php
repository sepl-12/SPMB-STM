@props([
    'label',
    'name',
    'value' => null,
    'required' => false,
    'helpText' => null,
    'error' => null,
])

<div
    x-data="signaturePad({
        field: '{{ $name }}',
        initialValue: @js($value),
        required: {{ $required ? 'true' : 'false' }},
    })"
    class="space-y-2"
>
    <label class="block text-sm font-medium text-gray-700">
        {{ $label }}
        @if($required)
            <span class="text-red-600">*</span>
        @endif
    </label>

    <div class="border border-gray-300 rounded-lg overflow-hidden bg-white relative">
        <canvas x-ref="canvas" class="w-full h-48 cursor-crosshair touch-none"></canvas>
        <div
            x-show="!hasValue"
            class="absolute inset-0 flex items-center justify-center pointer-events-none"
        >
            <span class="text-sm text-gray-400">Tandatangani di sini</span>
        </div>
    </div>

    <div class="flex flex-wrap gap-2 text-sm">
        <button
            type="button"
            class="px-3 py-2 bg-gray-100 rounded hover:bg-gray-200 transition"
            @click="clear()"
        >
            Bersihkan
        </button>
        <button
            type="button"
            class="px-3 py-2 bg-gray-100 rounded hover:bg-gray-200 transition"
            @click="undo()"
            x-show="hasValue"
        >
            Undo
        </button>
        <template x-if="previewUrl">
            <a
                :href="previewUrl"
                target="_blank"
                rel="noopener"
                class="px-3 py-2 bg-gray-100 rounded hover:bg-gray-200 transition"
            >
                Lihat
            </a>
        </template>
    </div>

    <input type="hidden" name="{{ $name }}" x-ref="input">

    @if($helpText)
        <p class="text-xs text-gray-500">{{ $helpText }}</p>
    @endif

    @if($error)
        <p class="text-xs text-red-600">{{ $error }}</p>
    @endif
</div>
