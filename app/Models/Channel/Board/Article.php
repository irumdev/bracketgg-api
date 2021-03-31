<?php

declare(strict_types=1);

namespace App\Models\Channel\Board;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Channel\Board\ArticleImage;
use App\Models\Common\Board\BaseArticle;
use App\Models\Channel\Board\Category;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends BaseArticle
{
    public const DEFAULT_SEE_COUNT = 0;
    public const DEFAULT_LIKE_COUNT = 0;
    public const DEFAULT_UN_LIKE_COUNT = 0;
    public const DEFAULT_COMMENT_COUNT = 0;

    use HasFactory;

    protected $articleImageModelName = ArticleImage::class;
    protected $table = 'channel_board_articles';
    protected $fillable = [
        'title', 'content', 'user_id',
        'category_id', 'see_count', 'like_count',
        'unlike_count', 'comment_count', 'channel_id'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
