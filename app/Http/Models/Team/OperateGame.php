<?php

declare(strict_types=1);

namespace App\Models\Team;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperateGame extends Model
{
    use HasFactory;
    protected $table = 'operate_games';
    protected $fillable = ['team_id', 'game_type_id'];
}
