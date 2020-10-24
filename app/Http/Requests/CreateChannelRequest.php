<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator as ValidContract;

use Symfony\Component\HttpFoundation\Response;


use App\Models\User;
use App\Helpers\ValidMessage;
use App\Helpers\ResponseBuilder;

class CreateChannelRequest extends FormRequest
{
    private const NAME_IS_EMPTY = 1;
    private const NAME_IS_NOT_STRING = 2;
    private const NAME_LENGTH_SHORT = 3;
    private const NAME_LENGTH_LONG = 4;
    private const NAME_IS_NOT_UNIQUE = 5;

    public const AUTORIZE_FAIL = 1;


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
        return [
            'name' => 'required|string|min:1|max:20|unique:App\Models\Channel,name'
        ];
    }


    public function messages(): array
    {
        return [
            'name.required' => json_encode(['code' => self::NAME_IS_EMPTY]),
            'name.string' => json_encode(['code' => self::NAME_IS_NOT_STRING]),
            'name.min' => json_encode(['code' => self::NAME_LENGTH_SHORT]),
            'name.max' => json_encode(['code' => self::NAME_LENGTH_LONG]),
            'name.unique' => json_encode(['code' => self::NAME_IS_NOT_UNIQUE]),
        ];
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

    private function buildAuthorizeErrorMessage(User $user): int
    {
        switch ($user) {
            case $user->can('createChannel') === false:
                $message = self::AUTORIZE_FAIL;
                break;

            default:
                $message = self::AUTORIZE_FAIL;
                break;
        }
        return $message;
    }
}
