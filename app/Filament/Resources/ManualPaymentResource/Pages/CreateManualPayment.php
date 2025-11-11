<?php

namespace App\Filament\Resources\ManualPaymentResource\Pages;

use App\Filament\Resources\ManualPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateManualPayment extends CreateRecord
{
    protected static string $resource = ManualPaymentResource::class;
}
