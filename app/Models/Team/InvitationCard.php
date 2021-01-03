<?php

declare(strict_types=1);

namespace App\Models\Team;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvitationCard extends Model
{
    /**
     * @var int 대기 상태
     */
    public const PENDING = 0;

    /**
     * @var int 수락 상태
     */
    public const ACCEPT = 1;

    /**
     * @var int 거절상태
     */
    public const REJECT = 2;

    use HasFactory;

    protected $table = 'team_member_invitation_cards';
    protected $fillable = [
        'user_id', 'team_id'
    ];
}
