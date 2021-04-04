<?php

declare(strict_types=1);

namespace App\Http\Requests\Rules;

class UploadBoardArticleComment
{
    /**
     * @var int 댓글 첨부 안함
     */
    public const COMMENT_IS_NOT_ATTACHED = 1;

    /**
     * @var int 댓글 문자열 아님
     */
    public const COMMENT_IS_NOT_STRING = 2;

    /**
     * @var int 답글 달 시 부모가 숫자가 아님
     */
    public const PARENT_ID_IS_NOT_NUMERIC = 3;

    /**
     * @var int 답글 달 시 존재하지 않는 댓글에 달려고 함
     */
    public const PARENT_IS_NOT_EXISTS = 4;

    public static function rules(string $replyModel): array
    {
        return [
            'content' => 'required|string',
            'parent_id' => 'nullable|numeric|exists:' . $replyModel . ',id',
        ];
    }

    public static function messages(): array
    {
        return [
            'content.required' => self::COMMENT_IS_NOT_ATTACHED,
            'content.string' => self::COMMENT_IS_NOT_STRING,
            'parent_id.numeric' => self::PARENT_ID_IS_NOT_NUMERIC,
            'parent_id.exists' => self::PARENT_IS_NOT_EXISTS,
        ];
    }
}
