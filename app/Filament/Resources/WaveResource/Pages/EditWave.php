<?php

namespace App\Filament\Resources\WaveResource\Pages;

use App\Filament\Resources\WaveResource;
use App\Models\Wave;
use Filament\Resources\Pages\EditRecord;

class EditWave extends EditRecord
{
    protected static string $resource = WaveResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function afterSave(): void
    {
        if (! $this->record->is_active) {
            return;
        }

        Wave::query()
            ->whereKeyNot($this->record->getKey())
            ->update(['is_active' => false]);
    }
}
