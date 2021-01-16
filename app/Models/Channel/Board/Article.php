<?php

declare(strict_types=1);

namespace App\Models\Channel\Board;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    public const DEFAULT_SEE_COUNT = 0;
    public const DEFAULT_LIKE_COUNT = 0;
    public const DEFAULT_UN_LIKE_COUNT = 0;
    public const DEFAULT_COMMENT_COUNT = 0;

    use HasFactory;

    protected $table = 'channel_board_articles';
    protected $fillable = [
        'title', 'content', 'user_id',
        'category_id', 'see_count', 'like_count',
        'unlike_count', 'comment_count'
    ];

}
