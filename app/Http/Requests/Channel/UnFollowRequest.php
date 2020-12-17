<?php

declare(strict_types=1);

namespace App\Http\Requests\Channel;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

/**
 * 팔로우 취소 요청을 검증하는 클래스 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class UnFollowRequest extends FormRequest
{
    /**
     * @var int 언팔로우 권한 없음
     */
    private const AUTORIZE_FAIL = 1;

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
        $this->user = Auth::user();
        $this->responseBuilder = $responseBuilder;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $user = $this->user;
        return $user &&
               $user->can('unFollowChannel');
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->responseBuilder->fail([
                'code' => $this->buildAuthorizeErrorMessage($this->user),
            ], Response::HTTP_FORBIDDEN)
        );
    }

    /**
     * 권한 없을 시, 권한에 관한 메세지를 빌드해주는 메소드 입니다.
     *
     * @param User $user 유저 모델
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return int $message 에러 코드
     */
    private function buildAuthorizeErrorMessage(User $user): int
    {
        $message = self::AUTORIZE_FAIL;
        switch ($user) {
            case $user->can('followChannel') === false:
                $message = self::AUTORIZE_FAIL;
                break;

            default:
                $message = self::AUTORIZE_FAIL;
                break;
        }
        return $message;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}
