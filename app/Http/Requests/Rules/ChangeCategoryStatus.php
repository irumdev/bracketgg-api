<?php

declare(strict_types=1);

namespace App\Http\Requests\Rules;

use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;

class ChangeCategoryStatus
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


    public static function rules(string $tableName, string $bindedName, string $permissionClass): array
    {
        $teamOrChannel = request()->route($bindedName);
        $boardCategories = $teamOrChannel->boardCategories;
        $boardCategoryRelatedKey = $boardCategories->first()->relatedKey;

        return [
            'needValidateItems.*.id' => [
                'bail',
                'nullable',
                'integer',
                Rule::exists($tableName)->where(function (Builder $query) use ($teamOrChannel, $boardCategoryRelatedKey): Builder {
                    return $query->where([
                        [$boardCategoryRelatedKey, '=', $teamOrChannel->id],
                    ]);
                })
            ],
            'needValidateItems.*.name' => [
                'bail',
                'required',
                'string',
                'category_name_is_not_unique:' . serialize($boardCategories),
            ],
            'needValidateItems.*.is_public' => 'bail|required|boolean',
            'needValidateItems.*.write_permission' => 'bail|required|integer|in:' . $permissionClass::getAllPermissions()->values()->implode(','),
            'needValidateItems.*.show_order' => 'bail|required|integer',
        ];
    }


    public static function messages(): array
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
