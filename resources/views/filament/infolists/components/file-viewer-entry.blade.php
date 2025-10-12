<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    @php
        $fileName = $getFileName();
        $fileSize = $getFileSize();
        $mimeType = $getMimeType();
        $downloadUrl = $getDownloadUrl();
        $previewUrl = $getPreviewUrl();
        $canPreview = $canPreview();
        $fileIcon = $getFileIcon();
        $fileIconColor = $getFileIconColor();
    @endphp

    @if($fileName)
        <div class="relative overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 transition-all hover:shadow-md">
            <div class="flex items-center gap-4 p-4">
                {{-- File Icon --}}
                <div class="flex-shrink-0">
                    <div class="flex h-14 w-14 items-center justify-center rounded-lg bg-{{ $fileIconColor }}-100 dark:bg-{{ $fileIconColor }}-900/20">
                        <x-filament::icon
                            :icon="$fileIcon"
                            class="h-8 w-8 text-{{ $fileIconColor }}-600 dark:text-{{ $fileIconColor }}-400"
                        />
                    </div>
                </div>

                {{-- File Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                                {{ $fileName }}
                            </h4>
                            <div class="mt-1 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <span class="inline-flex items-center gap-1">
                                    <x-filament::icon
                                        icon="heroicon-m-document"
                                        class="h-3 w-3"
                                    />
                                    {{ $mimeType }}
                                </span>
                                <span class="text-gray-300 dark:text-gray-600">•</span>
                                <span class="inline-flex items-center gap-1">
                                    <x-filament::icon
                                        icon="heroicon-m-arrow-down-tray"
                                        class="h-3 w-3"
                                    />
                                    {{ $fileSize }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                @if($downloadUrl)
                    <div class="flex items-center gap-2 flex-shrink-0">
                        @if($canPreview && $previewUrl)
                            <a
                                href="{{ $previewUrl }}"
                                target="_blank"
                                class="inline-flex items-center justify-center gap-1 rounded-lg px-3 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors"
                            >
                                <x-filament::icon
                                    icon="heroicon-m-eye"
                                    class="h-4 w-4"
                                />
                                <span class="hidden sm:inline">Preview</span>
                            </a>
                        @endif

                        <a
                            href="{{ $downloadUrl }}"
                            class="inline-flex items-center justify-center gap-1 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-800 transition-colors"
                        >
                            <x-filament::icon
                                icon="heroicon-m-arrow-down-tray"
                                class="h-4 w-4"
                            />
                            <span class="hidden sm:inline">Download</span>
                        </a>
                    </div>
                @endif
            </div>

            {{-- Preview Section (if PDF and can preview) --}}
            @if($canPreview && $previewUrl && str_contains($mimeType, 'pdf'))
                <div class="border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                    <div class="p-4">
                        <details class="group">
                            <summary class="flex cursor-pointer items-center justify-between text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100 transition-colors">
                                <span class="inline-flex items-center gap-2">
                                    <x-filament::icon
                                        icon="heroicon-m-document-magnifying-glass"
                                        class="h-4 w-4"
                                    />
                                    Lihat Preview Dokumen
                                </span>
                                <x-filament::icon
                                    icon="heroicon-m-chevron-down"
                                    class="h-4 w-4 transition-transform group-open:rotate-180"
                                />
                            </summary>
                            <div class="mt-4">
                                <div class="overflow-hidden rounded-lg border border-gray-300 dark:border-gray-600">
                                    <iframe
                                        src="{{ $previewUrl }}"
                                        class="w-full h-96 bg-white"
                                        frameborder="0"
                                    ></iframe>
                                </div>
                            </div>
                        </details>
                    </div>
                </div>
            @endif

            {{-- Preview Section (if Image and can preview) --}}
            @if($canPreview && $previewUrl && str_contains($mimeType, 'image'))
                <div class="border-t border-gray-200 dark:border-gray-700">
                    <div class="p-4">
                        <img
                            src="{{ $previewUrl }}"
                            alt="{{ $fileName }}"
                            class="w-full h-auto max-h-96 object-contain rounded-lg"
                        />
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="text-sm text-gray-500 dark:text-gray-400 italic">
            Tidak ada file yang diupload
        </div>
    @endif
</x-dynamic-component>
