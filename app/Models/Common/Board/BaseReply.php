<?php

namespace App\Models\Common\Board;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

abstract class BaseReply extends Model
{
    protected string $relatedParentModelName;
    protected string $relatedParentKeyName;
    protected string $realModelName;

    public function writer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function replies(): HasMany
    {
        throw_if(empty($this->realModelName), new LogicException('real model not found'));
        return $this->hasMany($this->realModelName, 'parent_id', 'id');
    }
}
