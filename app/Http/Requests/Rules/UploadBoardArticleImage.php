<?php

declare(strict_types=1);

namespace App\Http\Requests\Rules;

class UploadBoardArticleImage
{
    /**
     * @var int 이미지파일 첨부 안함
     */
    public const ARTICLE_IMAGE_IS_NOT_ATTACHED = 1;

    /**
     * @var int 첨부한 내용이 파일이 아님
     */
    public const ARTICLE_IMAGE_IS_NOT_FILE = 2;

    /**
     * @var int 첨부한 내용이 이미지가 아님
     */
    public const ARTICLE_IMAGE_IS_NOT_IMAGE = 3;

    /**
     * @var int 첨부한 파일이 mime가 올버르지 않음
     */
    public const ARTICLE_IMAGE_MIME_IS_NOT_VALID = 4;

    /**
     * @var int 첨부한 max 사이즈가 올바르지 않음
     */
    public const ARTICLE_IMAGE_IS_LARGE = 5;


    public static function rules(): array
    {
        return [
            'article_image' => 'required|file|image|mimes:jpeg,jpg,png|max:2048',
        ];
    }

    public static function messages(): array
    {
        return [
            'article_image.required' => self::ARTICLE_IMAGE_IS_NOT_ATTACHED,
            'article_image.file' => self::ARTICLE_IMAGE_IS_NOT_FILE,
            'article_image.image' => self::ARTICLE_IMAGE_IS_NOT_IMAGE,
            'article_image.mimes' => self::ARTICLE_IMAGE_MIME_IS_NOT_VALID,
            'article_image.max' => self::ARTICLE_IMAGE_IS_LARGE,
        ];
    }
}
