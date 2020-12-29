<?php

declare(strict_types=1);

namespace App\Models\Team;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvitationCards extends Model
{
    protected $table = 'team_member_invitation_cards';
    protected $fillable = [
        'user_id', 'team_id'
    ];
    use HasFactory;
}
