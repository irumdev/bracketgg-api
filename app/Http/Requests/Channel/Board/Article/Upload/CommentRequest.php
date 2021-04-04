<?php

declare(strict_types=1);

namespace App\Http\Requests\Channel\Board\Article\Upload;

use App\Http\Requests\CommonFormRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Wrappers\BoardWritePermission\Channel as ChannelArticleWritePermissions;
use App\Http\Requests\Rules\UploadBoardArticleComment;
use App\Models\Channel\Board\Reply;
use App\Helpers\ValidMessage;
use Illuminate\Contracts\Validation\Validator as ValidContract;
use Symfony\Component\HttpFoundation\Response;

class CommentRequest extends CommonFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $canAccessCategoryToAny = $this->route('channelBoardCategory')->is_public;

        if ($canAccessCategoryToAny) {
            return true;
        }

        return $this->route('slug')->owner === Auth::id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return UploadBoardArticleComment::rules(Reply::class);
    }

    public function messages(): array
    {
        return UploadBoardArticleComment::messages();
    }

    /**
     * 벨러데이션 실패 시 실행하는 메소드 입니다.
     *
     * @param ValidContract $validator 벨러데이터 인터페이스
     * @throws Illuminate\Http\Exceptions\HttpResponseException 422 처리불가 엔티티 익셉션
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return void
     */
    protected function failedValidation(ValidContract $validator): void
    {
        $this->throwUnProcessableEntityException(ValidMessage::first($validator));
    }

    /**
     * 채널 게시판에 게시글 작성권한 없을 시 실행되는 메소드 입니다
     *
     * @throws Illuminate\Http\Exceptions\HttpResponseException 401 인증실패 익셉션
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return void
     */
    protected function failedAuthorization(): void
    {
        $this->throwUnAuthorizedException(
            Response::HTTP_UNAUTHORIZED
        );
    }
}
