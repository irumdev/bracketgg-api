<?php

namespace App\Models\Channel\Board;

use App\Models\Channel\Channel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Common\Board\BaseReply;

class Reply extends BaseReply
{
    use HasFactory;
    protected $table = 'channel_article_replies';
    protected string $realModelName = Reply::class;
    protected string $relatedParentModelName = Channel::class;
    protected string $relatedParentKeyName = 'channel_id';
    protected $fillable = [
        'article_id', 'parent_id',
        'user_id', 'channel_id', 'content',
    ];
}
