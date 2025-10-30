<?php

namespace App\Filament\Resources\FormResource\Pages;

use App\Filament\Resources\FormResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditForm extends EditRecord
{
    protected static string $resource = FormResource::class;

    public function mount($record): void
    {
        parent::mount($record);

        $this->record->ensureActiveVersion()->loadMissing(['formSteps', 'formFields']);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getSubheading(): ?string
    {
        $version = $this->record->getCurrentFormVersion();

        if (! $version) {
            return null;
        }

        $status = $version->is_active ? 'aktif' : 'draft';

        return sprintf('Versi saat ini: v%s (%s)', $version->version_number, $status);
    }

    protected function publishActiveVersion(): void
    {
        $form = $this->record;
        $activeVersion = $form->ensureActiveVersion();

        $form->formVersions()
            ->whereKeyNot($activeVersion->getKey())
            ->update(['is_active' => false]);

        $activeVersion->update([
            'is_active' => true,
            'published_datetime' => now(),
        ]);

        Notification::make()
            ->title('Perubahan formulir diterbitkan')
            ->body('Struktur formulir telah diperbarui dan menjadi versi aktif.')
            ->success()
            ->send();
    }
}
