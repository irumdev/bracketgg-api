<?php

declare(strict_types=1);

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;

use App\Models\User;
use App\Models\Team\Team;
use App\Helpers\ResponseBuilder;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class ShowInfoRequest extends FormRequest
{
    /**
     * @var User 유저 인스턴스
     */
    private ?User $user;

    /**
     * @var Team 팀 인스턴스
     */
    private Team $team;

    /**
     * 응답 정형화를 위하여 사용되는 객체
     * @var ResponseBuilder 응답 정형화 객체
     */
    private ResponseBuilder $responseBuilder;

    public function __construct(ResponseBuilder $responseBuilder)
    {
        $this->responseBuilder = $responseBuilder;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $this->user = request()->user('sanctum');
        $this->team = $this->route('teamSlug');

        if ($this->user === null) {
            return $this->team->is_public;
        }

        if ($this->team->is_public === false) {
            return $this->user->can('viewTeam', $this->team);
        }

        return true;
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->responseBuilder->fail([
                'code' => Response::HTTP_UNAUTHORIZED,
            ], Response::HTTP_UNAUTHORIZED)
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [];
    }
}
