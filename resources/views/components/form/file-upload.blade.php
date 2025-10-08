@props([
    'label' => '',
    'name' => '',
    'value' => '',
    'required' => false,
    'helpText' => '',
    'error' => '',
    'accept' => '',
    'maxSize' => '2MB'
])

@php
    // Check if file already uploaded (value is path)
    $hasExistingFile = !empty($value) && is_string($value);
    $existingFileName = $hasExistingFile ? basename($value) : '';
@endphp

<div class="mb-4" x-data="{ fileName: '{{ $existingFileName }}', isDragging: false, hasExisting: {{ $hasExistingFile ? 'true' : 'false' }} }">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    <!-- Show existing file if any -->
    <div x-show="hasExisting && !fileName" class="mb-3 p-3 bg-green-50 border border-green-200 rounded-lg flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <div>
                <p class="text-sm font-medium text-green-900">File sudah diupload</p>
                <p class="text-xs text-green-700">{{ $existingFileName }}</p>
            </div>
        </div>
        <a href="{{ $hasExistingFile ? asset('storage/' . $value) : '#' }}" target="_blank" class="text-xs text-green-600 hover:text-green-700 font-medium">
            Lihat File
        </a>
    </div>
    
    <div 
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="
            isDragging = false;
            const files = $event.dataTransfer.files;
            if (files.length) {
                $refs.fileInput.files = files;
                fileName = files[0].name;
                hasExisting = false;
            }
        "
        :class="{ 'border-green-500 bg-green-50': isDragging }"
        class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors cursor-pointer"
        @click="$refs.fileInput.click()"
    >
        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <div class="mt-2">
            <p class="text-sm text-gray-600" x-show="!fileName">
                <span class="font-medium text-green-600">Klik untuk upload</span> atau drag & drop
            </p>
            <p class="text-sm text-gray-900 font-medium" x-show="fileName" x-text="fileName"></p>
            <p class="text-xs text-gray-500 mt-1">
                {{ $helpText ?: "Maks. $maxSize" }}
            </p>
        </div>
        <input
            x-ref="fileInput"
            type="file"
            id="{{ $name }}"
            name="{{ $name }}"
            class="hidden"
            {{ $required && !$hasExistingFile ? 'required' : '' }}
            @if($accept) accept="{{ $accept }}" @endif
            @change="fileName = $event.target.files[0]?.name || ''; hasExisting = false;"
        />
    </div>
    
    @if($error)
        <p class="mt-1 text-sm text-red-600">{{ $error }}</p>
    @endif
</div>
