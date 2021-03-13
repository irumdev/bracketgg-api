<?php

declare(strict_types=1);

namespace App\Http\Requests\Team\Board\Category;

use App\Models\User;
use App\Models\Team\Board\Category as TeamBoardCategory;
use App\Wrappers\BoardWritePermission\Team as TeamBoardWritePermission;
use App\Http\Requests\CommonFormRequest;
use App\Helpers\ValidMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Contracts\Validation\Validator as ValidContract;
use App\Http\Requests\Rules\ChangeCategoryStatus as ChangeCategoryStatusRule;

class ChangeStatusRequest extends CommonFormRequest
{

    /**
     * @var int CAN_NOT_CREATE_CATEGORY 카테고리 최대개수 초과로 생성 권한 없음
     */
    public const CAN_NOT_CREATE_CATEGORY = 1;

    /**
     * @var int CAN_NOT_UPDATE_CATEGORY 업데이트 또는 생성 권한 없음
     */
    public const CAN_NOT_UPDATE_CATEGORY = 2;

    /**
     * @var User $requestUser 카테고리 상태 변경 요청한 유저
     */
    private User $requestUser;

    /**
     * @var bool $canUpdateTeam 팀 게시판 카테고리 업데이트 가능 여부
     */
    private bool $canUpdateCategory;

    /**
     * @var bool $canCreateCategory 팀 게시판 카테고리 생성 가능 여부
     */
    private bool $canCreateCategory;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $this->requestUser = Auth::user();

        $team = $this->route('teamSlug');

        $allItems = collect($this->all());

        $allItems->forget('needValidateItems');
        $allItems->forget('doNotNeedValidate');

        $willCreateItem = collect($allItems)->filter(fn ($item): bool => isset($item['id']) === false);

        $this->canCreateCategory = $willCreateItem->count() < $team->board_category_count_limit;
        $this->canUpdateCategory = $this->requestUser->can('updateTeam', $team);

        return $this->canUpdateCategory && $this->canCreateCategory;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return ChangeCategoryStatusRule::rules(
            (new TeamBoardCategory())->getTable(),
            'teamSlug',
            TeamBoardWritePermission::class,
        );
    }

    public function prepareForValidation(): void
    {
        $team = $this->route('teamSlug');

        $preProcessedData = collect($this->all())->map(function (array $validateItem, int $showOrder) use ($team): array {
            $searchCondition = [
                ['id', '=', $validateItem['id'] ?? -1],
                ['team_id', '=',  $team->id],
                ['name', '=',  $validateItem['name'] ?? ''],
                ['show_order', '=',  $showOrder],
                ['is_public', '=', $validateItem['is_public'] ?? ''],
                ['write_permission', '=', $validateItem['write_permission'] ?? ''],
            ];

            $needUpdate = isset($validateItem['id']) ? (
                TeamBoardCategory::where($searchCondition)->exists() === false
            ) : false;

            return array_merge($validateItem, [
                'need_validate' => $needUpdate,
                'show_order' => $showOrder,
            ]);
        });

        $this->merge([
            'needValidateItems' => $preProcessedData->filter(function (array $needValidateItem): bool {
                return $needValidateItem['need_validate'];
            })->toArray(),
            'doNotNeedValidate' => $preProcessedData->filter(function (array $needValidateItem): bool {
                return ! $needValidateItem['need_validate'];
            })->toArray()
        ]);
    }

    protected function failedValidation(ValidContract $validator): void
    {
        $this->throwUnProcessableEntityException(ValidMessage::first($validator));
    }

    protected function failedAuthorization(): void
    {
        $this->throwUnAuthorizedException(
            $this->buildAuthorizeErrorMessage()
        );
    }

    public function buildAuthorizeErrorMessage(): int
    {
        switch (true) {
            case $this->canUpdateCategory === false:
                return self::CAN_NOT_UPDATE_CATEGORY;

            case $this->canCreateCategory === false:
                return self::CAN_NOT_CREATE_CATEGORY;
        }
    }

    public function messages(): array
    {
        return ChangeCategoryStatusRule::messages();
    }
}
