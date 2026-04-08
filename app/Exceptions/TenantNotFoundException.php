<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class TenantNotFoundException extends HttpException
{
    public function __construct(string $message = 'Tenant not found.')
    {
        parent::__construct(403, $message);
    }
}
