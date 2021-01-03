<?php

declare(strict_types=1);

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;

use App\Models\User;
use App\Models\Team\Team;

use App\Helpers\ValidMessage;

use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator as ValidContract;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\CommonFormRequest;

/**
 * 팀 로고이미지 업데이트 시 값 검증 클래스 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class UpdateLogoImageRequest extends CommonFormRequest
{
    /**
     * @var User 유저 인스턴스
     */
    private User $user;

    /**
     * @var Team 팀 생성가능 여부
     */
    private Team $team;

    /**
     * @var int 로고가 파일이 아님
     */
    public const LOGO_IS_NOT_FILE = 1;

    /**
     * @var int 로고가 이미지가 아님
     */
    public const LOGO_IS_NOT_IMAGE = 2;

    /**
     * @var int 로고이미지 MIME가 틀림
     */
    public const LOGO_MIME_IS_NOT_MATCH = 3;

    /**
     * @var int 로고이미지 최대크기 초과
     */
    public const LOGO_IS_IMAGE_IS_LARGE = 4;

    /**
     * @var int 로고이미지 첨부하지 않음
     */
    public const LOGO_IS_NOT_ATTACHED = 5;

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
        return $this->user->can('updateTeam', $this->team);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'logo_image' => 'required|file|mimes:jpeg,jpg,png|image|max:2048',
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
            'logo_image.required' => self::LOGO_IS_NOT_ATTACHED,
            'logo_image.file' => self::LOGO_IS_NOT_FILE,
            'logo_image.image' => self::LOGO_IS_NOT_IMAGE,
            'logo_image.mimes' => self::LOGO_MIME_IS_NOT_MATCH,
            'logo_image.max' => self::LOGO_IS_IMAGE_IS_LARGE,
        ];
    }
}
