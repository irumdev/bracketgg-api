<?php

declare(strict_types=1);

namespace App\Models\Team;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvitationCard extends Model
{
    use SoftDeletes;
    use HasFactory;

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

    protected $table = 'team_member_invitation_cards';
    protected $fillable = [
        'user_id', 'team_id'
    ];
    protected $dates = ['deleted_at'];
}
