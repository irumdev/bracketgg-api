<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Team\Team;
use App\Services\TeamService;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseBuilder;
use App\Http\Requests\Team\CheckNameExistsRequest;

class CheckTeamNameExistsController extends Controller
{
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
