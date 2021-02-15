<?php

declare(strict_types=1);

namespace App\Models\Team\Board;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Common\Board\BaseCategory;

class Category extends BaseCategory
{
    protected $table = 'team_board_categories';
    protected $articleModel = Article::class;
    protected $fillable = [
        'name', 'show_order', 'article_count', 'is_public', 'team_id'
    ];

    protected $casts = [
        'is_public' => 'bool'
    ];

    use HasFactory;
}
