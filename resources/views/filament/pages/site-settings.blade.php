<x-filament-panels::page>
    <form wire:submit.prevent="save">
        {{ $this->form }}

        <div class="mt-6 flex items-center justify-end gap-x-4">
            <x-filament::button
                type="submit"
                size="lg"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="save">
                    <x-filament::icon
                        icon="heroicon-o-check-circle"
                        class="w-5 h-5 mr-2 inline"
                    />
                    Simpan Semua Perubahan
                </span>
                <span wire:loading wire:target="save">
                    <x-filament::loading-indicator class="w-5 h-5 mr-2 inline" />
                    Menyimpan...
                </span>
            </x-filament::button>
        </div>
    </form>

    <x-filament-actions::modals />
</x-filament-panels::page>
