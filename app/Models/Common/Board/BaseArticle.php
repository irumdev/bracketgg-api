<?php

declare(strict_types=1);

namespace App\Models\Common\Board;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Symfony\Component\Process\Exception\LogicException;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

abstract class BaseArticle extends Model
{
    protected $articleImageModelName;
    protected string $articleCommentModelName;

    public string $relateKeyName;
    public array $eagerRelation = [
        'images:id,article_id,article_image',
        'writer:id,nick_name,profile_image',
    ];

    public function images(): HasMany
    {
        throw_if(empty($this->articleImageModelName), new LogicException('model name is empty'));
        return $this->hasMany($this->articleImageModelName, 'article_id', 'id');
    }

    public function writer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function comments(): HasMany
    {
        $canNotRelateToCommentModel = empty($this->articleCommentModelName);
        throw_if($canNotRelateToCommentModel, new LogicException('model or key name name is empty'));
        return $this->hasMany(
            $this->articleCommentModelName,
            'article_id',
            'id'
        );
    }
}
