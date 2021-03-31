<?php

declare(strict_types=1);

namespace App\Http\Requests\Rules;

class UploadBoardArticle
{
    /**
     * @var int 게시글 첨부 안함
     */
    public const ARTICLE_IS_NOT_ATTACHED = 1;

    /**
     * @var int 게시글 내용이 문자열 아님
     */
    public const ARTICLE_IS_NOT_STRING = 2;

    /**
     * @var int 게시글 제목이 첨부 안함
     */
    public const ARTICLE_TITLE_IS_NOT_ATTACHED = 3;

    /**
     * @var int 게시글 제목이 문자열 아님
     */
    public const ARTICLE_TITLE_IS_NOT_STRING = 4;


    public static function rules(): array
    {
        $baseStringRule = 'required|string';
        return [
            'article' => $baseStringRule,
            'title' => $baseStringRule
        ];
    }

    public static function beforeValidation(array $dirtyMarkup): string
    {
        return trim(clean($dirtyMarkup['article'] ?? ''));
    }

    public static function messages(): array
    {
        return [
            'article.required' => self::ARTICLE_IS_NOT_ATTACHED,
            'article.string' => self::ARTICLE_IS_NOT_STRING,

            'title.required' => self::ARTICLE_TITLE_IS_NOT_ATTACHED,
            'title.string' => self::ARTICLE_IS_NOT_STRING,
        ];
    }
}
