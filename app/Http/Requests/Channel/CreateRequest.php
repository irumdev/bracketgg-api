<?php

declare(strict_types=1);

namespace App\Http\Requests\Channel;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator as ValidContract;

use App\Models\User;
use App\Helpers\ValidMessage;
use App\Helpers\ResponseBuilder;
use App\Http\Requests\Rules\CreateChannel as CreateChannelRule;

/**
 * 채널 생성 시 유효성 검증 클래스 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class CreateRequest extends FormRequest
{
    /**
     * @var int 채널생성조건에 불충분
     */
    public const CAN_NOT_CREATE_CHANNEL = 1;

    /**
     * @var int 인증되지 않은 이메일로 요청
     */
    public const HAS_NOT_VERIFY_EMAIL = 2;

    /**
     * 응답 정형화를 위하여 사용되는 객체
     * @var ResponseBuilder 응답 정형화 객체
     */
    private ResponseBuilder $responseBuilder;

    /**
     * @var User 유저 모델
     */
    private User $user;

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
        return $this->user->can('createChannel');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return CreateChannelRule::rules();
    }

    public function messages(): array
    {
        return CreateChannelRule::messages();
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->responseBuilder->fail([
                'code' => $this->buildAuthorizeErrorMessage($this->user),
            ], Response::HTTP_UNAUTHORIZED)
        );
    }

    protected function failedValidation(ValidContract $validator): void
    {
        throw new HttpResponseException(
            $this->responseBuilder->fail(ValidMessage::first($validator))
        );
    }

    /**
     * 권한 없을 시, 권한에 관한 메세지를 빌드해주는 메소드 입니다.
     *
     * @todo 공통화
     * @param User $user 유저 모델
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return int $message 에러 코드
     */
    private function buildAuthorizeErrorMessage(User $user): int
    {
        switch ($user) {
            case $user->hasVerifiedEmail() === false:
                $message = self::HAS_NOT_VERIFY_EMAIL;
                break;

            case $user->can('createChannel') === false:
                $message = self::CAN_NOT_CREATE_CHANNEL;
                break;

            default:
                $message = self::CAN_NOT_CREATE_CHANNEL;
                break;
        }
        return $message;
    }
}
