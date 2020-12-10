<?php

declare(strict_types=1);

namespace App\Http\Requests\Team;

use App\Models\User;
use App\Helpers\ValidMessage;
use App\Helpers\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use App\Models\Team\Slug as TeamSlug;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator as ValidContract;

/**
 * 이미지를 제외한 팀 정보 업데이트 데이터 검증 클래스 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class UpdateInfoWithOutBannerRequest extends FormRequest
{
    /**
     * @var int 슬러그 중복
     */
    public const SLUG_IS_NOT_UNIQUE = 1;

    /**
     * @var int 슬러그 길이 짧음
     */
    public const SLUG_IS_SHORT = 2;

    /**
     * @var int 슬러그 김
     */
    public const SLUG_IS_LONG = 3;

    /**
     * @var int 슬러그 패턴 불일지
     */
    public const SLUG_PATTERN_IS_NOT_MATCH = 4;

    /**
     * @var int 공개여부가 bool이 아님
     */
    public const PUBLIC_STATUS_IS_NOT_BOOLEAN = 5;

    /**
     * @var int 게임카테고리가 배열이 아님
     */
    public const GAME_CATEGORY_IS_NOT_ARRAY = 6;

    /**
     * @var int 게임 카테고리 아이템이 스트링이 아님
     */
    public const GAME_CATEGORY_ITEM_IS_NOT_STRING = 7;

    /**
     * @var int 게임 카테고리가 짧음
     */
    public const GAME_CATEGORY_ITEM_IS_SHORT = 8;

    /**
     * @var int 게임 카테고리가 김
     */
    public const GAME_CATEGORY_ITEM_IS_LONG = 9;

    /**
     * @var User 유저 인스턴스
     */
    private User $user;

    /**
     * @var bool 팀 생성가능 여부
     */
    private bool $canUpdate = false;

    /**
     * 응답 정형화를 위하여 사용되는 객체
     * @var ResponseBuilder 응답 정형화 객체
     */
    private ResponseBuilder $responseBuilder;


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

    /**
     * 기존 젱슨 에러 형태로 전환해주는 메소드 입니다.
     *
     * @param int $code 에러코드
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return string 제이슨 형태의 에러구조
     */
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
