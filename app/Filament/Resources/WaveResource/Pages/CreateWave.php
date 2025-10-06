<?php

namespace App\Filament\Resources\WaveResource\Pages;

use App\Filament\Resources\WaveResource;
use App\Models\Wave;
use Filament\Resources\Pages\CreateRecord;

class CreateWave extends CreateRecord
{
    protected static string $resource = WaveResource::class;

    protected function afterCreate(): void
    {
        $this->syncActiveState();
    }

    protected function syncActiveState(): void
    {
        if (! $this->record->is_active) {
            return;
        }

        Wave::query()
            ->whereKeyNot($this->record->getKey())
            ->update(['is_active' => false]);
    }
}
