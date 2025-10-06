<?php

namespace App\Filament\Resources\FormResource\Pages;

use App\Filament\Resources\FormResource;
use App\Models\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Str;

class ListForms extends ListRecords
{
    protected static string $resource = FormResource::class;

    public function mount(): void
    {
        parent::mount();

        $form = Form::query()->first();

        if (! $form) {
            $form = Form::create([
                'form_name' => 'Formulir Pendaftaran',
                'form_code' => Str::slug('Formulir Pendaftaran'),
            ]);

            $form->ensureActiveVersion();

            Notification::make()
                ->title('Formulir baru dibuat')
                ->success()
                ->send();
        }

        $this->redirect(FormResource::getUrl('edit', ['record' => $form]));
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
