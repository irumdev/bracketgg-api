<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request): ?string
    {
        throw new UnauthorizedException(null, Response::HTTP_UNAUTHORIZED);
        return null;
    }
}
