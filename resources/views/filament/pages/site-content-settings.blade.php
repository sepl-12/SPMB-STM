<x-filament-panels::page>
    <form wire:submit.prevent="submit" class="space-y-6">
        {{ $this->form }}

        <div>
            <x-filament::button type="submit">
                Simpan perubahan
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
