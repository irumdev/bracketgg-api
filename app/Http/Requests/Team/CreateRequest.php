<?php

declare(strict_types=1);

namespace App\Http\Requests\Team;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Exceptions\HttpResponseException;

use App\Models\User;
use App\Http\Requests\Rules\CreateTeam as CreateTeamRules;
use App\Helpers\ValidMessage;
use App\Helpers\ResponseBuilder;
use App\Http\Requests\Rules\CreateChannel as CreateChannelRules;
use Illuminate\Contracts\Validation\Validator as ValidContract;

class CreateRequest extends FormRequest
{
    private ResponseBuilder $responseBuilder;
    private User $user;
    private bool $canCreateTeam = false;

    public const CAN_NOT_CREATE_TEAM = 1;
    public const HAS_NOT_VERIFY_EMAIL = 2;

    public function __construct(ResponseBuilder $responseBuilder)
    {
        $this->responseBuilder = $responseBuilder;
        $this->user = Auth::user();
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $this->canCreateTeam = $this->user->can('createTeam');
        return $this->canCreateTeam;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return CreateTeamRules::rules();
    }

    public function messages(): array
    {
        return CreateChannelRules::messages();
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->responseBuilder->fail([
                'code' => $this->buildAuthorizeErrorMessage($this->user),
            ], Response::HTTP_UNAUTHORIZED)
        );
    }

    protected function failedValidation(ValidContract $validator): void
    {
        throw new HttpResponseException(
            $this->responseBuilder->fail(ValidMessage::first($validator))
        );
    }

    /**
     * @todo 팀 한정 이 메소드 헬퍼로 분리 후 공통화 작업.
     */
    public function buildAuthorizeErrorMessage(User $user): int
    {
        switch ($user) {
            case $user->hasVerifiedEmail() === false:
                $message = self::HAS_NOT_VERIFY_EMAIL;
                break;

            case $this->canCreateTeam === false:
                $message = self::CAN_NOT_CREATE_TEAM;
                break;

            default:
                $message = self::CAN_NOT_CREATE_TEAM;
                break;
        }
        return $message;
    }
}
