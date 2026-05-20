<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class InsufficientCreditsException extends HttpException
{
    public function __construct()
    {
        parent::__construct(400, "User does not have enough credits");
    }
}
