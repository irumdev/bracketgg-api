<?php

declare(strict_types=1);

namespace App\Http\Requests\Channel\Board;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\CommonFormRequest;
use Illuminate\Contracts\Validation\Validator as ValidContract;
use App\Helpers\ValidMessage;
use App\Models\Channel\Board\Category;

class ShowArticleRequest extends CommonFormRequest
{
    public const CATEGORY_IS_REQUIRED = 1;

    public const CATEGORY_IS_NOT_EXISTS = 2;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
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
        ];
    }
}
