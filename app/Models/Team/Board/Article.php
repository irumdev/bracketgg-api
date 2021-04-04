<?php

declare(strict_types=1);

namespace App\Models\Team\Board;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Common\Board\BaseArticle;
use App\Models\Team\Board\ArticleImage;
use App\Models\Team\Board\Category;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Team\Board\Reply as TeamArticleReply;

class Article extends BaseArticle
{
    public const DEFAULT_SEE_COUNT = 0;
    public const DEFAULT_LIKE_COUNT = 0;
    public const DEFAULT_UN_LIKE_COUNT = 0;
    public const DEFAULT_COMMENT_COUNT = 0;

    use HasFactory;

    protected $table = 'team_board_articles';
    protected $articleImageModelName = ArticleImage::class;
    protected string $articleCommentModelName = TeamArticleReply::class;

    protected $fillable = [
        'title', 'content', 'user_id',
        'category_id', 'see_count', 'like_count',
        'unlike_count', 'comment_count', 'team_id'
    ];
    public string $relateKeyName = 'team_id';

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
