<?php

declare(strict_types=1);

namespace App\Http\Requests\Team;

use App\Models\User;
use App\Models\Team\Team;
use App\Helpers\ValidMessage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Query\Builder;
use App\Models\Team\BannerImage as TeamBannerImage;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Contracts\Validation\Validator as ValidContract;
use App\Http\Requests\CommonFormRequest;

/**
 * 팀 베너 업데이트 전 데이터 유효성 검증 클래스 입니다.
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class UpdateBannerImageRequest extends CommonFormRequest
{
    /**
     * @var int 업로드한 파일이 이미지가 아님
     */
    public const BANNER_UPLOAD_FILE_IS_NOT_IMAGE = 1;

    /**
     * @var int 업로드 한 파일의 MIME가 일치하지 않음
     */
    public const BANNER_UPLOAD_FILE_MIME_IS_NOT_MATCH = 2;

    /**
     * @var int 업로드한 파일의 사이즈가 최대사이즈 초과
     */
    public const BANNER_UPLOAD_FILE_IS_LARGE = 3;

    /**
     * @var int 이미지가 온전히 업로드 되지 않음
     */
    public const BANNER_UPLOAD_IS_NOT_FULL_UPLOADED_FILE = 4;

    /**
     * @var int 베너아이디가 숫자가 아님
     */
    public const BANNER_IMAGE_ID_IS_NOT_NUMERIC = 5;

    /**
     * @var int 존재하지 않는 배너 아이디
     */
    public const BANNER_IMAGE_ID_IS_NOT_EXISTS = 6;

    /**
     * @var int 배너 이미지를 업로드 하지 않음
     */
    public const BANNER_UPLOAD_FILE_IS_NOT_ATTACHED = 7;

    /**
     * @var int 배너이미지를 이미 가지고 있음
     */
    public const BANNER_UPLOAD_FILE_HAS_MANY_BANNER = 8;

    /**
     * @var User 유저 인스턴스
     */
    private User $user;

    /**
     * @var bool 팀 배너 업데이트 가능 여부
     */
    private bool $canUpdate = false;

    /**
     * @var Team 팀 인스턴스
     */
    private Team $team;

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
        $this->team = $this->route('teamSlug');
        $this->canUpdate = $this->user->can('updateTeam', $this->team);
        return $this->canUpdate;
    }

    protected function failedAuthorization(): void
    {
        $this->throwUnAuthorizedException(
            Response::HTTP_UNAUTHORIZED
        );
    }

    protected function failedValidation(ValidContract $validator): void
    {
        $this->throwUnProcessableEntityException(ValidMessage::first($validator));
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
                'teamHasOnlyOneBanner'
            ],
            'banner_image_id' => [
                'nullable',
                'numeric',
                Rule::exists((new TeamBannerImage())->getTable(), 'id')->where(function (Builder $query) {
                    $query->where('team_id', $this->team->id);
                }),
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'banner_image.required' => self::BANNER_UPLOAD_FILE_IS_NOT_ATTACHED,
            'banner_image.file' => self::BANNER_UPLOAD_IS_NOT_FULL_UPLOADED_FILE,
            'banner_image.image' => self::BANNER_UPLOAD_FILE_IS_NOT_IMAGE,
            'banner_image.mimes' => self::BANNER_UPLOAD_FILE_MIME_IS_NOT_MATCH,
            'banner_image.max' => self::BANNER_UPLOAD_FILE_IS_LARGE,
            'banner_image.team_has_only_one_banner' => self::BANNER_UPLOAD_FILE_HAS_MANY_BANNER,

            'banner_image_id.numeric' => self::BANNER_IMAGE_ID_IS_NOT_NUMERIC,
            'banner_image_id.exists' => self::BANNER_IMAGE_ID_IS_NOT_EXISTS,

        ];
    }
}
