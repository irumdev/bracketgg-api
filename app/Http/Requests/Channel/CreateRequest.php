<?php

declare(strict_types=1);

namespace App\Http\Requests\Channel;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator as ValidContract;

use App\Models\User;
use App\Helpers\ValidMessage;
use App\Helpers\ResponseBuilder;
use App\Http\Requests\Rules\CreateChannel as CreateChannelRule;

class CreateRequest extends FormRequest
{
    public const CAN_NOT_CREATE_CHANNEL = 1;
    public const HAS_NOT_VERIFY_EMAIL = 2;

    private ResponseBuilder $responseBuilder;
    private User $user;

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
        return $this->user->can('createChannel');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return CreateChannelRule::rules();
    }

    public function messages(): array
    {
        return CreateChannelRule::messages();
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
     * @todo 공통화
     */
    private function buildAuthorizeErrorMessage(User $user): int
    {
        switch ($user) {
            case $user->hasVerifiedEmail() === false:
                $message = self::HAS_NOT_VERIFY_EMAIL;
                break;

            case $user->can('createChannel') === false:
                $message = self::CAN_NOT_CREATE_CHANNEL;
                break;

            default:
                $message = self::CAN_NOT_CREATE_CHANNEL;
                break;
        }
        return $message;
    }
}
