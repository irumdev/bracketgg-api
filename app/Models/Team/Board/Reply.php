<?php

namespace App\Models\Team\Board;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Common\Board\BaseReply;
use App\Models\Team\Team;

class Reply extends BaseReply
{
    use HasFactory;

    protected $table = 'team_article_replies';
    protected string $realModelName = Reply::class;
    protected string $relatedParentModelName = Team::class;
    protected string $relatedParentKeyName = 'team_id';
    protected $fillable = [
        'article_id', 'parent_id',
        'user_id', 'team_id', 'content',
    ];
}
