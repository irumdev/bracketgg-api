<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;

class EmailVerificationRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $requestUser = User::findOrFail($this->route('id'));
        if ($requestUser->email_verified_at !== null) {
            return false;
        }

        return hash_equals(
            $this->route('hash'),
            sha1($requestUser->getEmailForVerification())
        );
    }



    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}
