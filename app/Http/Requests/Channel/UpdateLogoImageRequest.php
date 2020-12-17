<?php

declare(strict_types=1);

namespace App\Http\Requests\Channel;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator as ValidContract;
use Symfony\Component\HttpFoundation\Response;

use App\Models\User;
use App\Models\Channel\Channel;

use App\Helpers\ValidMessage;
use App\Helpers\ResponseBuilder;

/**
 * 로고이미지 업데이트 요청 검증 객체 입니다.
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class UpdateLogoImageRequest extends FormRequest
{
    /**
     * 응답 정형화를 위하여 사용되는 객체
     * @var ResponseBuilder 응답 정형화 객체
     */
    private ResponseBuilder $responseBuilder;

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
        return [
            'logo_image' => 'nullable|file|image|mimes:jpeg,jpg,png|max:2048',
        ];
    }

    protected function failedValidation(ValidContract $validator): void
    {
        $this->errorResponse(ValidMessage::first($validator));
    }

    protected function failedAuthorization(): void
    {
        $this->errorResponse([
            'code' => Response::HTTP_UNAUTHORIZED,
        ], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * 에러메세지를 클라이언트에게 리턴할 공통 메소드 입니다.
     *
     * @param mixed $errorMessage 응답할 에러메세지
     * @param int $httpStatus 응답할 response code
     * @throws HttpResponseException 클라이언트에게 응답하기 위한 익셉션 객체
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return void
     */
    private function errorResponse($errorMessage, int $httpStatus = Response::HTTP_UNPROCESSABLE_ENTITY): void
    {
        throw new HttpResponseException(
            $this->responseBuilder->fail($errorMessage, $httpStatus)
        );
    }

    public function messages(): array
    {
        return [
            'logo_image.image' => json_encode(['code' => self::LOGO_UPLOAD_FILE_IS_NOT_IMAGE]),
            'logo_image.file' => json_encode(['code' => self::LOGO_UPLOAD_IS_NOT_FULL_UPLOADED_FILE]),
            'logo_image.mimes' => json_encode(['code' => self::LOGO_UPLOAD_FILE_MIME_IS_NOT_MATCH]),
            'logo_image.max' => json_encode(['code' => self::LOGO_UPLOAD_FILE_IS_LARGE]),
        ];
    }
}
