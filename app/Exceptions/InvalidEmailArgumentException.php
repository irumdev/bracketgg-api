<?php

namespace App\Exceptions;

use Exception;
use App\Helpers\ResponseBuilder;
use Symfony\Component\HttpFoundation\Response;


class InvalidEmailArgumentException extends Exception
{
}
