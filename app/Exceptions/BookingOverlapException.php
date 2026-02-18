<?php

namespace App\Exceptions;

use Exception;

class BookingOverlapException extends Exception
{
    /**
     * Create a new booking overlap exception.
     */
    public function __construct(string $message = 'The booking overlaps with an existing booking.')
    {
        parent::__construct($message);
    }
}
