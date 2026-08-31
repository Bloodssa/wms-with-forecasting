<?php

namespace App\Exceptions;

use Exception;

class WarrantyOperationException extends Exception
{
    protected ?string $redirectRoute;

    public function __construct(string $message, ?string $redirectRoute = null)
    {
        parent::__construct($message);

        $this->redirectRoute = $redirectRoute;
    }

    public function redirectRoute(): ?string
    {
        return $this->redirectRoute;
    }
}
