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

class ChangeStatusRequest extends CommonFormRequest
{

    /**
     * @var int CATEGORY_ID_IS_NOT_INTEGER 카테고리 아이디가 숫자가 아님
     */
    public const CATEGORY_ID_IS_NOT_INTEGER = 1;

    /**
     * @var int CATEGORY_ID_IS_NOT_EXISTS 존재하지 안는 카테고리 아이디
     */
    public const CATEGORY_ID_IS_NOT_EXISTS = 2;

    /**
     * @var int CATEGORY_NAME_IS_NOT_STRING 카테고리 이름이 숫자가 아님
     */
    public const CATEGORY_NAME_IS_NOT_STRING = 3;

    /**
     * @var int CATEGORY_NAME_IS_EMPTY 카테고리 이름이 비어있음
     */
    public const CATEGORY_NAME_IS_EMPTY = 4;

    /**
     * @var int CATEGORY_NAME_IS_DUPLICATE 중복된 카테고리 이름
     */
    public const CATEGORY_NAME_IS_DUPLICATE = 5;

    /**
     * @var int PUBLIC_STATUS_IS_NOT_BOOLEAN 공개여부가 boolean 이 아님
     */
    public const PUBLIC_STATUS_IS_NOT_BOOLEAN = 6;

    /**
     * @var int PUBLIC_STATUS_IS_EMPTY 공개여부가 비어있음
     */
    public const PUBLIC_STATUS_IS_EMPTY = 7;

    /**
     * @var int WRITE_PERMISSION_IS_EMPTY 직성권한이 비어있음
     */
    public const WRITE_PERMISSION_IS_EMPTY = 8;

    /**
     * @var int WRITE_PERMISSION_IS_NOT_INTEGER 직성권한이 숫자가 아님
     */
    public const WRITE_PERMISSION_IS_NOT_INTEGER = 9;

    /**
     * @var int WRITE_PERMISSION_IS_NOT_ALLOWED_POLICY 올바른 작성권한이 아님
     */
    public const WRITE_PERMISSION_IS_NOT_ALLOWED_POLICY = 10;

    /**
     * @var int SHOW_ORDER_IS_NOT_INTEGER 올바른 작성권한이 아님
     */
    public const SHOW_ORDER_IS_NOT_INTEGER = 11;

    /**
     * @var int SHOW_ORDER_IS_EMPTY 정렬 순서가 비어있음
     */
    public const SHOW_ORDER_IS_EMPTY = 12;

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
    private bool $canUpdateTeamCategory;

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
        $this->canUpdateTeamCategory = $this->requestUser->can('updateTeam', $team);



        return $this->canUpdateTeamCategory && $this->canCreateCategory;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        /**
         * @todo id가 없는게 현재 팀의 보드카테고리 리밋 넘어 갈 시 422
         */
        $tableName = (new TeamBoardCategory())->getTable();
        $team = $this->route('teamSlug');

        return [
            'needValidateItems.*.id' => [
                'bail',
                'nullable',
                'integer',
                Rule::exists($tableName)->where(function (Builder $query) use ($team): Builder {
                    return $query->where([
                        ['team_id', '=', $team->id],
                    ]);
                })
            ],
            'needValidateItems.*.name' => [
                'bail',
                'required',
                'string',
                'category_name_is_not_unique:' . serialize($team->boardCategories),
            ],
            'needValidateItems.*.is_public' => 'bail|required|boolean',
            'needValidateItems.*.write_permission' => 'bail|required|integer|in:' . TeamBoardWritePermission::getAllPermissions()->values()->implode(','),
            'needValidateItems.*.show_order' => 'bail|required|integer',
        ];
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
            case $this->canUpdateTeamCategory === false:
                return self::CAN_NOT_UPDATE_CATEGORY;

            case $this->canCreateCategory === false:
                return self::CAN_NOT_CREATE_CATEGORY;
        }
    }

    public function messages(): array
    {
        return [
            'needValidateItems.*.id.integer' => self::CATEGORY_ID_IS_NOT_INTEGER,
            'needValidateItems.*.id.exists' => self::CATEGORY_ID_IS_NOT_EXISTS,

            'needValidateItems.*.name.string' => self::CATEGORY_NAME_IS_NOT_STRING,
            'needValidateItems.*.name.required' => self::CATEGORY_NAME_IS_EMPTY,
            'needValidateItems.*.name.category_name_is_not_unique' => self::CATEGORY_NAME_IS_DUPLICATE,

            'needValidateItems.*.is_public.boolean' => self::PUBLIC_STATUS_IS_NOT_BOOLEAN,
            'needValidateItems.*.is_public.required' => self::PUBLIC_STATUS_IS_EMPTY,

            'needValidateItems.*.write_permission.required' => self::WRITE_PERMISSION_IS_EMPTY,
            'needValidateItems.*.write_permission.integer' => self::WRITE_PERMISSION_IS_NOT_INTEGER,
            'needValidateItems.*.write_permission.in' => self::WRITE_PERMISSION_IS_NOT_ALLOWED_POLICY,

            'needValidateItems.*.show_order.required' => self::SHOW_ORDER_IS_EMPTY,
            'needValidateItems.*.show_order.integer' => self::SHOW_ORDER_IS_NOT_INTEGER,

        ];
    }
}
