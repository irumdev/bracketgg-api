<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use App\Helpers\ResponseBuilder;
use Symfony\Component\HttpFoundation\Response;

/**
 * 이메일 api 리턴값이 올바르지 않을때 throw되는 class입니다.
 *
 * @author  dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class InvalidEmailArgumentException extends Exception
{
}
