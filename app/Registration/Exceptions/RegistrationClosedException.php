<?php

namespace App\Registration\Exceptions;

class RegistrationClosedException extends \RuntimeException
{
    public function __construct(string $message = 'Gelombang pendaftaran tidak aktif.')
    {
        parent::__construct($message);
    }
}
