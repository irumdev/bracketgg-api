<?php

declare(strict_types=1);

namespace App\Models\Team;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 팀의 멤버 모델 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class Member extends Model
{
    use HasFactory;

    protected $table = 'team_members';
    protected $fillable = [
        'team_id', 'user_id'
    ];
}
