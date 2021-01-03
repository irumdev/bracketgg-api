<?php

declare(strict_types=1);

namespace App\Http\Requests\Channel;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Channel\Fan as ChannelFan;
use App\Http\Requests\CommonFormRequest;

/**
 * 채널 좋아요 취소 요청 검증 클래스 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class UnLikeRequest extends CommonFormRequest
{
    /**
     * @var User 유저 모델
     */
    private User $user;

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
        $user = $this->user;
        return $user && $user->can('unLikeChannel');
    }

    protected function failedAuthorization(): void
    {
        $this->throwUnAuthorizedException(
            $this->buildAuthorizeErrorMessage($this->user)
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
        $message = ChannelFan::AUTORIZE_FAIL;
        switch ($user) {
            case $user->can('likeChannel') === false:
                $message = ChannelFan::AUTORIZE_FAIL;
                break;

            default:
                $message = ChannelFan::AUTORIZE_FAIL;
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
        return [];
    }
}
