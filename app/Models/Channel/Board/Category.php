<?php

declare(strict_types=1);

namespace App\Models\Channel\Board;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Channel\Board\Article;

class Category extends Model
{
    use HasFactory;

    protected $table = 'channel_board_categories';
    protected $fillable = [
        'name', 'show_order', 'article_count', 'is_public', 'channel_id'
    ];

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'article_id', 'id');
    }
}
