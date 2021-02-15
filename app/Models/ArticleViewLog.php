<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleViewLog extends Model
{
    use HasFactory;

    public const CHANNEL_ARTICLE = 1;
    public const TEAM_ARTICLE = 2;

    protected $fillable = [
        'lookup_ip', 'article_type', 'article_id'
    ];
}
