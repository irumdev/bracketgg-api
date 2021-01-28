<?php

declare(strict_types=1);

namespace App\Models\Team\Board;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleImageBuffer extends Model
{
    use HasFactory;

    protected $table = 'team_article_images_buffers';
}
