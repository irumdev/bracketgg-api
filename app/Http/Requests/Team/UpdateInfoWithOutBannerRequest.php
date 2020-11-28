<?php

declare(strict_types=1);

namespace App\Http\Requests\Team;

use App\Models\User;
use App\Helpers\ValidMessage;
use App\Helpers\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use App\Models\Team\Slug as TeamSlug;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Requests\Rules\CreateTeam as CreateTeamRules;
use Illuminate\Contracts\Validation\Validator as ValidContract;

class UpdateInfoWithOutBannerRequest extends FormRequest
{
    public const SLUG_IS_NOT_UNIQUE = 1;
    public const SLUG_IS_SHORT = 2;
    public const SLUG_IS_LONG = 3;
    public const SLUG_PATTERN_IS_NOT_MATCH = 4;

    public const PUBLIC_STATUS_IS_NOT_BOOLEAN = 5;

    public const GAME_CATEGORY_IS_NOT_ARRAY = 6;
    public const GAME_CATEGORY_ITEM_IS_NOT_STRING = 7;
    public const GAME_CATEGORY_ITEM_IS_SHORT = 8;
    public const GAME_CATEGORY_ITEM_IS_LONG = 9;

    private User $user;
    private bool $canUpdate;

    public function __construct(ResponseBuilder $responseBuilder)
    {
        $this->responseBuilder = $responseBuilder;
        $this->canUpdate = false;
        $this->user = Auth::user();
    }
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    public function authorize(): bool
    {
        $this->canUpdate = $this->user->can('updateTeam', $this->route('teamSlug'));
        return $this->canUpdate;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'slug' => [
                'nullable', 'string',
                'min:' . TeamSlug::MIN_SLUG_LENGTH,
                'max:' . TeamSlug::MAX_SLUG_LENGTH,
                /**
                 * 패턴은 첫글자에 영어 소문자 포함
                 * 이후에는 엉여 대소문자, 숫자, - 포함
                 */
                'regex:/^(([a-z]{1}).*(\-?)*(\d*))/',
                'unique:App\Models\Team\Slug,slug'
            ],
            'is_public' => 'nullable|boolean',
            'games' => 'nullable|array',
            'games.*' => 'string|min:1|max:255',

        ];
    }

    private function toErrStructure(int $code): string
    {
        return json_encode(['code' => $code]);
    }

    public function messages(): array
    {
        return [
            'slug.min' => $this->toErrStructure(self::SLUG_IS_SHORT),
            'slug.max' => $this->toErrStructure(self::SLUG_IS_LONG),
            'slug.unique' =>$this->toErrStructure(self::SLUG_IS_NOT_UNIQUE),
            'slug.regex' => $this->toErrStructure(self::SLUG_PATTERN_IS_NOT_MATCH),

            'is_public.boolean' => $this->toErrStructure(self::PUBLIC_STATUS_IS_NOT_BOOLEAN),

            'games.array' => $this->toErrStructure(self::GAME_CATEGORY_IS_NOT_ARRAY),
            'games.*.string' => $this->toErrStructure(self::GAME_CATEGORY_ITEM_IS_NOT_STRING),
            'games.*.min' => $this->toErrStructure(self::GAME_CATEGORY_ITEM_IS_SHORT),
            'games.*.max' => $this->toErrStructure(self::GAME_CATEGORY_ITEM_IS_LONG),
        ];
    }

    protected function failedValidation(ValidContract $validator): void
    {
        throw new HttpResponseException(
            $this->responseBuilder->fail(ValidMessage::first($validator))
        );
    }
}
