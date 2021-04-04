<?php

namespace App\Models\Team\Board;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    use HasFactory;

    protected $table = 'team_article_replies';
    protected $fillable = [
        'article_id', 'parent_id',
        'user_id', 'team_id', 'content',
    ];
}
