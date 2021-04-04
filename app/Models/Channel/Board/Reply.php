<?php

namespace App\Models\Channel\Board;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    use HasFactory;
    protected $table = 'channel_article_replies';
    protected $fillable = [
        'article_id', 'parent_id',
        'user_id', 'channel_id', 'content',
    ];
}
