<?php

declare(strict_types=1);

namespace App\Models\Channel\Board;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Channel\Board\Article;
use App\Models\Channel\Channel;
use App\Models\Common\Board\BaseCategory;

class Category extends BaseCategory
{
    use HasFactory;

    protected $table = 'channel_board_categories';
    protected $fillable = [
        'name', 'show_order', 'article_count', 'is_public', 'channel_id', 'write_permission'
    ];

    protected $casts = [
        'is_public' => 'bool'
    ];
    public $articleModel = Article::class;
    public $relatedKey = 'channel_id';
}
