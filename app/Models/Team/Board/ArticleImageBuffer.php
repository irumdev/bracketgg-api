<?php

declare(strict_types=1);

namespace App\Models\Team\Board;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @deprecated
 * @todo 추후 리팩토링 시 사용 예정
 */
class ArticleImageBuffer extends Model
{
    use HasFactory;

    protected $table = 'team_article_images_buffers';
}
