<?php

declare(strict_types=1);

namespace App\Models\Team\Board;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    protected $table = 'team_board_categories';
    use HasFactory;

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'category_id', 'id');
    }
}
