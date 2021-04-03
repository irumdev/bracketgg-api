<?php

declare(strict_types=1);

namespace App\Models\Team;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    /**
     * @var int 이미 팀원인 상태
     */
    public const ALREADY_TEAM_MEMBER = 3;

    /**
     * @var int 팀장이 일반유저에게 팀원초대
     */
    public const FROM_TEAM_OWNER = 1;

    /**
     * @var int 팀장이 일반유저에게 팀원초대
     */
    public const FROM_NORMAL_USER = 2;

    protected $table = 'team_member_invitation_cards';
    protected $fillable = [
        'user_id', 'team_id',
        'from_type', 'invitation_card_creator'
    ];

    protected $dates = ['deleted_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
