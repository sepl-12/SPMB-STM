<?php

namespace App\Filament\Resources\ExportTemplateResource\Pages;

use App\Filament\Resources\ExportTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExportTemplate extends CreateRecord
{
    protected static string $resource = ExportTemplateResource::class;

    protected function afterCreate(): void
    {
        $this->syncDefaultFlag();
    }

    protected function syncDefaultFlag(): void
    {
        if (! $this->record->is_default || ! $this->record->form) {
            return;
        }

        $this->record->form
            ->exportTemplates()
            ->whereKeyNot($this->record->getKey())
            ->update(['is_default' => false]);
    }
}
