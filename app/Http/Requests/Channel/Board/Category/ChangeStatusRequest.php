<?php

declare(strict_types=1);

namespace App\Http\Requests\Channel\Board\Category;

use App\Http\Requests\CommonFormRequest;

class ChangeStatusRequest extends CommonFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return false;
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
