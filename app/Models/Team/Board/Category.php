<?php

declare(strict_types=1);

namespace App\Models\Team\Board;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Common\Board\BaseCategory;
use App\Models\Team\Team;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends BaseCategory
{
    use HasFactory;

    protected $table = 'team_board_categories';
    protected $articleModel = Article::class;

    protected $fillable = [
        'name', 'show_order', 'article_count', 'is_public', 'team_id', 'write_permission'
    ];
    protected $casts = [
        'is_public' => 'bool'
    ];

    public $relatedKey = 'team_id';

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }
}
