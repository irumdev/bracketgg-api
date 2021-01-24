<?php

declare(strict_types=1);

namespace App\Http\Requests\Channel;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator as ValidContract;
use Symfony\Component\HttpFoundation\Response;

use App\Models\User;
use App\Models\Channel\Channel;
use App\Models\Channel\Slug as ChannelSlug;
use App\Models\Channel\Broadcast as ChannelBroadCast;
use App\Http\Requests\Rules\Broadcast as BroadcastRules;
use App\Wrappers\UpdateBroadcastTypeWrapper;
use App\Http\Requests\Rules\CreateChannel as CreateChannelRule;

use App\Helpers\ValidMessage;
use App\Http\Requests\Rules\Slug;

use App\Http\Requests\CommonFormRequest;

/**
 * 채널정보 업데이트를 위한 요청 검증 클래스 입니다.
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class UpdateRequest extends CommonFormRequest
{
    /**
     * @var User 유저 모델
     */
    private User $user;

    /**
     * @var Channel 채널 모델
     */
    private Channel $channel;

    /**
     * @var int 슬러그 형태가 스트링이 아님
     */
    public const SLUG_IS_NOT_STRING = 6;

    /**
     * @var int 슬러그가 짧음
     */
    public const SLUG_IS_SHORT = 7;

    /**
     * @var int 슬러그 자리수 초괴
     */
    public const SLUG_IS_LONG = 8;

    /**
     * @var int 슬러그 패턴 일치하지 않음
     */
    public const SLUG_PATTERN_IS_WRONG = 9;

    /**
     * @var int 채널설명이 문자열이 아님
     */
    public const DESCRIPTION_IS_NOT_STRING = 10;

    /**
     * @var int 슬러그기 중복됨
     */
    public const SLUG_IS_NOT_UNIQUE = 21;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $this->channel = $this->route('slug');
        return $this->user->can('updateChannel', [
            $this->channel,
        ]);
    }

    protected function failedAuthorization(): void
    {
        $this->throwUnAuthorizedException(
            Response::HTTP_UNAUTHORIZED
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $channelNameRule = CreateChannelRule::rules()['name'];

        $broadcastRules = BroadcastRules::broadcastRules(new UpdateBroadcastTypeWrapper(
            ChannelBroadCast::$platforms,
            'isMyChannelBroadcast',
            ChannelBroadCast::class
        ));

        return array_merge($broadcastRules, [
            'slug' => [
                'nullable', 'string',
                'min:' . ChannelSlug::MIN_SLUG_LENGTH,
                'max:' . ChannelSlug::MAX_SLUG_LENGTH,
                /**
                 * 패턴은 첫글자에 영어 소문자 포함
                 * 이후에는 엉여 대소문자, 숫자, - 포함
                 */
                'regex:' . Slug::PATTERN,
                'unique:App\Models\Channel\Slug,slug'
            ],
            'name' => array_replace(explode('|', $channelNameRule), [0 => 'nullable']),
            'description' => 'nullable|string',
        ]);
    }

    protected function failedValidation(ValidContract $validator): void
    {
        $this->throwUnProcessableEntityException(ValidMessage::first($validator));
    }

    public function messages(): array
    {
        $channelNameRuleRequireToNullable = Arr::changeKey(CreateChannelRule::messages(), 'name.required', 'name.nullable');
        return array_merge($channelNameRuleRequireToNullable, BroadcastRules::messages(), [
            'slug.string' => self::SLUG_IS_NOT_STRING,
            'slug.min' => self::SLUG_IS_SHORT,
            'slug.max' => self::SLUG_IS_LONG,
            'slug.regex' => self::SLUG_PATTERN_IS_WRONG,
            'slug.unique' => self::SLUG_IS_NOT_UNIQUE,

            'description.string' => self::DESCRIPTION_IS_NOT_STRING,
        ]);
    }
}
