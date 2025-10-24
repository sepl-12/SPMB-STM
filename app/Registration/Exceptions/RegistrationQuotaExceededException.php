<?php

namespace App\Registration\Exceptions;

class RegistrationQuotaExceededException extends \RuntimeException
{
    public function __construct(string $message = 'Kuota pendaftaran untuk gelombang ini sudah penuh.')
    {
        parent::__construct($message);
    }
}
