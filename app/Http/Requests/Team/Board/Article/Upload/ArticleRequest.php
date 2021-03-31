<?php

declare(strict_types=1);

namespace App\Http\Requests\Team\Board\Article\Upload;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Wrappers\BoardWritePermission\Team as TeamArticleWritePermissions;
use App\Http\Requests\Rules\UploadBoardArticle;
use App\Helpers\ValidMessage;
use Illuminate\Contracts\Validation\Validator as ValidContract;
use App\Http\Requests\CommonFormRequest;
use Symfony\Component\HttpFoundation\Response;

class ArticleRequest extends CommonFormRequest
{
    /**
     * @var User $requestUser 요청한 유저 인스턴스
     */
    private User $requestUser;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $this->requestUser = Auth::user();
        $boardCategory = $this->route('teamBoardCategory');

        switch ($boardCategory->write_permission) {
            case TeamArticleWritePermissions::ONLY_OWNER:
                return $this->requestUser->can('updateTeam', [
                    $this->route('teamSlug')
                ]);

            case TeamArticleWritePermissions::OWNER_AND_MEMBER:
                return $this->requestUser->can('viewTeam', $this->route('teamSlug'));

            case TeamArticleWritePermissions::ALL_USER:
                return true;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return UploadBoardArticle::rules();
    }

    public function messages(): array
    {
        return UploadBoardArticle::messages();
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'article' => UploadBoardArticle::beforeValidation(
                $this->only('article')
            )
        ]);
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
