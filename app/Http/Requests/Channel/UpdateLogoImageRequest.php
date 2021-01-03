<?php

declare(strict_types=1);

namespace App\Http\Requests\Channel;

use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator as ValidContract;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\CommonFormRequest;


use App\Models\User;
use App\Models\Channel\Channel;

use App\Helpers\ValidMessage;

/**
 * 로고이미지 업데이트 요청 검증 객체 입니다.
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class UpdateLogoImageRequest extends CommonFormRequest
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
     * @var int 업로드한 로고이미지가 사진이 아님
     */
    public const LOGO_UPLOAD_FILE_IS_NOT_IMAGE = 1;

    /**
     * @var int 업로드한 파일의 MIME가 일치하지 않음
     */
    public const LOGO_UPLOAD_FILE_MIME_IS_NOT_MATCH = 2;

    /**
     * @var int 업로드한 로고 파일이 큼
     */
    public const LOGO_UPLOAD_FILE_IS_LARGE = 3;

    /**
     * @var int 로고 파일이 업로드가 완전히 안됨
     */
    public const LOGO_UPLOAD_IS_NOT_FULL_UPLOADED_FILE = 4;

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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'logo_image' => 'nullable|file|image|mimes:jpeg,jpg,png|max:2048',
        ];
    }

    protected function failedValidation(ValidContract $validator): void
    {
        $this->throwUnProcessableEntityException(ValidMessage::first($validator));
    }

    protected function failedAuthorization(): void
    {
        $this->throwUnAuthorizedException(
            Response::HTTP_UNAUTHORIZED
        );
    }

    public function messages(): array
    {
        return [
            'logo_image.image' => self::LOGO_UPLOAD_FILE_IS_NOT_IMAGE,
            'logo_image.file' => self::LOGO_UPLOAD_IS_NOT_FULL_UPLOADED_FILE,
            'logo_image.mimes' => self::LOGO_UPLOAD_FILE_MIME_IS_NOT_MATCH,
            'logo_image.max' => self::LOGO_UPLOAD_FILE_IS_LARGE,
        ];
    }
}
