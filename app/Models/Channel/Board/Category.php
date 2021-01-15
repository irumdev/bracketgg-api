<?php

declare(strict_types=1);

namespace App\Models\Channel\Board;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'channel_board_categories';
}
