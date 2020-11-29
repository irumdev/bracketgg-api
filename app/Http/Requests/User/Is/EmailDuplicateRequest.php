<?php

declare(strict_types=1);

namespace App\Http\Requests\User\Is;

use Illuminate\Foundation\Http\FormRequest;

use App\Helpers\ResponseBuilder;
use App\Helpers\ValidMessage;
use Illuminate\Contracts\Validation\Validator as ValidContract;
use Illuminate\Http\Exceptions\HttpResponseException;

class EmailDuplicateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    private ResponseBuilder $response;
    public function __construct(ResponseBuilder $response)
    {
        $this->response = $response;
    }
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'email' => 'unique:App\Models\User,email',
        ];
    }

    protected function failedValidation(ValidContract $validator): void
    {
        throw new HttpResponseException(
            $this->response->fail(ValidMessage::first($validator))
        );
    }

    public function messages(): array
    {
        return [
            'email.unique' => json_encode([
                'isDuplicate' => true
            ])
        ];
    }
}
