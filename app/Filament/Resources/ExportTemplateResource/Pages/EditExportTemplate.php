<?php

namespace App\Filament\Resources\ExportTemplateResource\Pages;

use App\Filament\Resources\ExportTemplateResource;
use Filament\Resources\Pages\EditRecord;

class EditExportTemplate extends EditRecord
{
    protected static string $resource = ExportTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function afterSave(): void
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
