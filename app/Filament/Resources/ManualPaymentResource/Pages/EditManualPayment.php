<?php

namespace App\Filament\Resources\ManualPaymentResource\Pages;

use App\Filament\Resources\ManualPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditManualPayment extends EditRecord
{
    protected static string $resource = ManualPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
