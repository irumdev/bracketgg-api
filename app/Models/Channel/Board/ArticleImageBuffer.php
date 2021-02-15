<?php

declare(strict_types=1);

namespace App\Models\Channel\Board;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @deprecated
 * @todo 추후 리팩토링 시 사용 예정
 */
class ArticleImageBuffer extends Model
{
    use HasFactory;

    protected $table = 'channel_board_article_images_buffer';
    protected $fillable = [
        'buffer_image_path'
    ];
}
