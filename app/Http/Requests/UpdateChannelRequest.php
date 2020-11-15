<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator as ValidContract;

use App\Models\User;
use App\Models\Channel\Channel;
use App\Models\Channel\Slug as ChannelSlug;
use App\Models\Channel\BannerImage as ChannelBannerImage;

use App\Http\Requests\Rules\CreateChannel as CreateChannelRule;

use App\Helpers\ValidMessage;
use App\Helpers\ResponseBuilder;

class UpdateChannelRequest extends FormRequest
{
    private ResponseBuilder $responseBuilder;
    private User $user;
    private Channel $channel;

    public const SLUG_IS_NOT_STRING = 6;
    public const SLUG_IS_SHORT = 7;
    public const SLUG_IS_LONG = 8;
    public const SLUG_PATTERN_IS_WRONG = 9;
    public const DESCRIPTION_IS_NOT_STRING = 10;

    public const PROFILE_UPLOAD_FILE_IS_NOT_IMAGE = 11;
    public const PROFILE_UPLOAD_FILE_MIME_IS_NOT_MATCH = 12;
    public const PROFILE_UPLOAD_FILE_IS_LARGE = 13;
    public const PROFILE_UPLOAD_IS_NOT_FULL_UPLOADED_FILE = 14;

    public const BANNER_UPLOAD_FILE_IS_NOT_IMAGE = 15;
    public const BANNER_UPLOAD_FILE_MIME_IS_NOT_MATCH = 16;
    public const BANNER_UPLOAD_FILE_IS_LARGE = 17;
    public const BANNER_UPLOAD_IS_NOT_FULL_UPLOADED_FILE = 18;

    public const BANNER_ID_IS_EMPTY = 19;
    public const BANNER_ID_IS_NOT_EXISTS = 20;


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
        $this->channel = $this->route('slug');
        return $this->user->can('updateChannel', [
            $this->channel,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $channelNameRule = CreateChannelRule::rules()['name'];
        return [
            'slug' => [
                'nullable', 'string',
                'min:' . ChannelSlug::MIN_SLUG_LENGTH,
                'max:' . ChannelSlug::MAX_SLUG_LENGTH,
                /**
                 * 패턴은 첫글자에 영어 소문자 포함
                 * 이후에는 엉여 대소문자, 숫자, - 포함
                 */
                'regex:/^(([a-z]{1}).*(\-?)*(\d*))/'
            ],
            'name' => array_replace(explode('|', $channelNameRule), [0 => 'nullable']),
            'description' => 'nullable|string',
            'logo_image' => 'nullable|file|image|mimes:jpeg,jpg,png|max:2048',
            'banner_image' => 'nullable|file|image|mimes:jpeg,jpg,png|max:2048',
            'banner_image_id' => [
                'required_with:banner_image',
                Rule::exists((new ChannelBannerImage())->getTable(), 'id')->where(function (Builder $query) {
                    $query->where('channel_id', $this->channel->id);
                }),
            ],
        ];
    }

    protected function failedValidation(ValidContract $validator): void
    {
        throw new HttpResponseException(
            $this->responseBuilder->fail(ValidMessage::first($validator))
        );
    }

    public function messages(): array
    {
        $channelNameRuleRequireToNullable = Arr::changeKey(CreateChannelRule::messages(), 'name.required', 'name.nullable');
        return array_merge($channelNameRuleRequireToNullable, [
            'slug.string' => json_encode(['code' => self::SLUG_IS_NOT_STRING]),
            'slug.min' => json_encode(['code' => self::SLUG_IS_SHORT]),
            'slug.max' => json_encode(['code' => self::SLUG_IS_LONG]),
            'slug.regex' => json_encode(['code' => self::SLUG_PATTERN_IS_WRONG]),

            'description.string' => json_encode(['code' => self::DESCRIPTION_IS_NOT_STRING]),

            'logo_image.image' => json_encode(['code' => self::PROFILE_UPLOAD_FILE_IS_NOT_IMAGE]),
            'logo_image.file' => json_encode(['code' => self::PROFILE_UPLOAD_IS_NOT_FULL_UPLOADED_FILE]),
            'logo_image.mimes' => json_encode(['code' => self::PROFILE_UPLOAD_FILE_MIME_IS_NOT_MATCH]),
            'logo_image.max' => json_encode(['code' => self::PROFILE_UPLOAD_FILE_IS_LARGE]),

            'banner_image_id.required_with' => json_encode(['code' => self::BANNER_ID_IS_EMPTY]),
            'banner_image_id.exists' => json_encode(['code' => self::BANNER_ID_IS_NOT_EXISTS]),
            'banner_image.image' => json_encode(['code' => self::BANNER_UPLOAD_FILE_IS_NOT_IMAGE]),
            'banner_image.file' => json_encode(['code'  => self::BANNER_UPLOAD_IS_NOT_FULL_UPLOADED_FILE]),
            'banner_image.mimes' => json_encode(['code' => self::BANNER_UPLOAD_FILE_MIME_IS_NOT_MATCH]),
            'banner_image.max' => json_encode(['code'   => self::BANNER_UPLOAD_FILE_IS_LARGE]),

        ]);
    }
}
