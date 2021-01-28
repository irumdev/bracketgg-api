<?php

declare(strict_types=1);

namespace App\Models\Team\Board;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleImage extends Model
{
    use HasFactory;

    protected $table = 'team_board_article_images';
}
