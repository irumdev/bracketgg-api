<?php

declare(strict_types=1);

namespace App\Http\Controllers\Team;

use App\Helpers\ResponseBuilder;
use App\Http\Controllers\Controller;
use App\Http\Requests\Team\CheckNameExistsRequest;

class CheckTeamNameExistsController extends Controller
{
    /**
     * @var ResponseBuilder $responseBuilder
     */
    private ResponseBuilder $responseBuilder;

    public function __construct(ResponseBuilder $responseBuilder)
    {
        $this->responseBuilder = $responseBuilder;
    }

    public function nameAlreadyExists(CheckNameExistsRequest $request)
    {
        return $this->responseBuilder->ok([
            'isDuplicate' => false,
        ]);
    }
}
