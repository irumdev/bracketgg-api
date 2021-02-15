<?php

declare(strict_types=1);

namespace App\Models\Common\Board;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Symfony\Component\Process\Exception\LogicException;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

abstract class BaseCategory extends Model
{
    protected $articleModel;

    public function articles(): HasMany
    {
        throw_if(empty($this->articleModel), new LogicException('article model is not defined'));
        return $this->hasMany($this->articleModel, 'category_id', 'id');
    }
}
