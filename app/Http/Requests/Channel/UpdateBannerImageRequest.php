<?php

declare(strict_types=1);

namespace App\Http\Requests\Channel;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Query\Builder;
use App\Models\Channel\BannerImage as ChannelBannerImage;
use App\Models\Channel\Channel;
use Illuminate\Contracts\Validation\Validator as ValidContract;
use App\Helpers\ValidMessage;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\CommonFormRequest;

/**
 * 베너 이미지 업데이트 요청 검증 클래스 입니다.
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class UpdateBannerImageRequest extends CommonFormRequest
{
    /**
     * @var int 배너 업로드 파일이 이미지가 아님
     */
    public const BANNER_UPLOAD_FILE_IS_NOT_IMAGE = 1;

    /**
     * @var int 업로드한 파일의 mime가 일치하지 않음
     */
    public const BANNER_UPLOAD_FILE_MIME_IS_NOT_MATCH = 2;

    /**
     * @var int 배너이미지가 용량이 큼
     */
    public const BANNER_UPLOAD_FILE_IS_LARGE = 3;

    /**
     * @var int 배너이미지가 파일이 아니거나 완전히 업로드 안됨
     */
    public const BANNER_UPLOAD_IS_NOT_FULL_UPLOADED_FILE = 4;

    /**
     * @var int 업데이트 할 배너 아이디가 숫자가 아님
     */
    public const BANNER_IMAGE_ID_IS_NOT_NUMERIC = 5;

    /**
     * @var int 나의 배너 아이디가 존재하지 않음
     */
    public const BANNER_IMAGE_ID_IS_NOT_EXISTS = 6;

    /**
     * @var int 배너 파일이 첨부가 안됨
     */
    public const BANNER_UPLOAD_FILE_IS_NOT_ATTACHED = 7;

    /**
     * @var int 이미 배너이미지가 한개 있음
     */
    public const BANNER_UPLOAD_FILE_HAS_MANY_BANNER = 8;

    /**
     * @var User 유저 모델
     */
    private User $user;

    /**
     * @var Channel 채널 모델
     */
    private Channel $channel;

    /**
     * @var bool 채널 업데이트 여부
     */
    private bool $canUpdate;

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
        $this->canUpdate = $this->user->can('updateChannel', [
            $this->channel = $this->route('slug')
        ]);
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
            'banner_image' => [
                'required',
                'file',
                'image',
                'mimes:jpeg,jpg,png',
                'max:2048',
                'channelHasOnlyOneBanner'
            ],
            'banner_image_id' => [
                'nullable',
                'numeric',
                Rule::exists((new ChannelBannerImage())->getTable(), 'id')->where(function (Builder $query): void {
                    $query->where('channel_id', $this->channel->id);
                }),
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'banner_image.required' => self::BANNER_UPLOAD_FILE_IS_NOT_ATTACHED,
            'banner_image.image' => self::BANNER_UPLOAD_FILE_IS_NOT_IMAGE,
            'banner_image.file' => self::BANNER_UPLOAD_IS_NOT_FULL_UPLOADED_FILE,
            'banner_image.mimes' => self::BANNER_UPLOAD_FILE_MIME_IS_NOT_MATCH,
            'banner_image.max' => self::BANNER_UPLOAD_FILE_IS_LARGE,

            'banner_image_id.numeric' => self::BANNER_IMAGE_ID_IS_NOT_NUMERIC,
            'banner_image_id.exists' => self::BANNER_IMAGE_ID_IS_NOT_EXISTS,
            'banner_image.channel_has_only_one_banner' => self::BANNER_UPLOAD_FILE_HAS_MANY_BANNER,
        ];
    }

    protected function failedAuthorization(): void
    {
        $this->throwUnAuthorizedException(
            Response::HTTP_UNAUTHORIZED,
        );
    }

    protected function failedValidation(ValidContract $validator): void
    {
        $this->throwUnProcessableEntityException(ValidMessage::first($validator));
    }
}
