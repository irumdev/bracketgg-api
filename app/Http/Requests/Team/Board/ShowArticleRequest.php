<?php

declare(strict_types=1);

namespace App\Http\Requests\Team\Board;

use App\Http\Requests\CommonFormRequest;
use App\Helpers\ValidMessage;
use App\Models\Team\Board\Category;

use Illuminate\Contracts\Validation\Validator as ValidContract;

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
            'category' => 'bail|required|hasCategory:teamSlug,team_id,' . Category::class
        ];
    }

    /**
     * @override
     */
    public function all($keys = null): array
    {
        return array_merge(request()->all(), [
            'category' => $this->route('teamBoardCategory'),
        ]);
    }

    public function messages(): array
    {
        return [
            'category.required' => self::CATEGORY_IS_REQUIRED,
            'category.has_category' => self::CATEGORY_IS_NOT_EXISTS,
        ];
    }

    protected function failedValidation(ValidContract $validator): void
    {
        $this->throwUnProcessableEntityException(ValidMessage::first($validator));
    }
}
