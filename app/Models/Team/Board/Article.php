<?php

declare(strict_types=1);

namespace App\Models\Team\Board;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Common\Board\BaseArticle;
use App\Models\Team\Board\ArticleImage;

class Article extends BaseArticle
{
    public const DEFAULT_SEE_COUNT = 0;
    public const DEFAULT_LIKE_COUNT = 0;
    public const DEFAULT_UN_LIKE_COUNT = 0;
    public const DEFAULT_COMMENT_COUNT = 0;

    use HasFactory;

    protected $table = 'team_board_articles';
    protected $articleImageModelName = ArticleImage::class;
}