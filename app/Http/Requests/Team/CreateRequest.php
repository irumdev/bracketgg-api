<?php

declare(strict_types=1);

namespace App\Http\Requests\Team;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Exceptions\HttpResponseException;

use App\Models\User;
use App\Helpers\ValidMessage;
use App\Helpers\ResponseBuilder;
use App\Http\Requests\Rules\CreateChannel as CreateChannelRules;
use Illuminate\Contracts\Validation\Validator as ValidContract;

class CreateRequest extends FormRequest
{
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
        return $this->user->can('createTeam');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $rules  = CreateChannelRules::rules();
        $rules = explode('|', $rules);
        // unique:App\Models\Channel,name
        dd(
            Arr::replaceItemByKey($rules, count($rules) - 1, 'unique:App\Models\Team\Team,name')
        );
        return $rules;
    }

    public function messages(): array
    {
        return [
            ''
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
}
