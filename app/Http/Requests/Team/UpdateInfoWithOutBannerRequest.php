<?php

declare(strict_types=1);

namespace App\Http\Requests\Team;

use App\Models\User;
use App\Helpers\ValidMessage;
use Illuminate\Support\Facades\Auth;
use App\Models\Team\Slug as TeamSlug;
use App\Models\Team\Broadcast as TeamBroadcast;
use App\Models\Team\Team;
use Illuminate\Contracts\Validation\Validator as ValidContract;
use App\Http\Requests\Rules\Slug;
use App\Http\Requests\Rules\Broadcast as BroadcastRules;
use App\Wrappers\UpdateBroadcastTypeWrapper;
use App\Http\Requests\CommonFormRequest;

/**
 * 이미지를 제외한 팀 정보 업데이트 데이터 검증 클래스 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class UpdateInfoWithOutBannerRequest extends CommonFormRequest
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
     * @var int 팀 이름이 문자열이 아님
     */
    public const TEAM_NAME_IS_NOT_STRING = 10;

    /**
     * @var int 팀 이름이 문자열이 아님
     */
    public const TEAM_NAME_IS_NOT_UNIQUE = 11;


    /**
     * @var User 유저 인스턴스
     */
    private User $user;

    /**
     * @var bool 팀 생성가능 여부
     */
    private bool $canUpdate = false;

    public function __construct()
    {
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
        $broadcastRules = BroadcastRules::broadcastRules(new UpdateBroadcastTypeWrapper(
            TeamBroadcast::$platforms,
            'isMyTeamBroadcast',
            TeamBroadcast::class
        ));
        return array_merge($broadcastRules, [
            'slug' => [
                'nullable', 'string',
                'min:' . TeamSlug::MIN_SLUG_LENGTH,
                'max:' . TeamSlug::MAX_SLUG_LENGTH,
                /**
                 * 패턴은 첫글자에 영어 소문자 포함
                 * 이후에는 엉여 대소문자, 숫자, - 포함
                 */
                'regex:' . Slug::PATTERN,
                'unique:App\Models\Team\Slug,slug'
            ],
            'name' => 'nullable|string|unique:' . sprintf("%s,%s", Team::class, 'name'),
            'is_public' => 'nullable|boolean',
            'games' => 'nullable|array',
            'games.*' => 'string|min:1|max:255',
        ]);
    }

    public function messages(): array
    {
        return array_merge([
            'slug.min' => self::SLUG_IS_SHORT,
            'slug.max' => self::SLUG_IS_LONG,
            'slug.unique' =>self::SLUG_IS_NOT_UNIQUE,
            'slug.regex' => self::SLUG_PATTERN_IS_NOT_MATCH,

            'is_public.boolean' => self::PUBLIC_STATUS_IS_NOT_BOOLEAN,

            'games.array' => self::GAME_CATEGORY_IS_NOT_ARRAY,
            'games.*.string' => self::GAME_CATEGORY_ITEM_IS_NOT_STRING,
            'games.*.min' => self::GAME_CATEGORY_ITEM_IS_SHORT,
            'games.*.max' => self::GAME_CATEGORY_ITEM_IS_LONG,

            'name.string' => self::TEAM_NAME_IS_NOT_STRING,
            'name.unique' => self::TEAM_NAME_IS_NOT_UNIQUE,
        ], BroadcastRules::messages());
    }

    protected function failedValidation(ValidContract $validator): void
    {
        $this->throwUnProcessableEntityException(ValidMessage::first($validator));
    }
}
