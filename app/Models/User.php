<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\URL;
/**
 * @todo event로 빼놓기
 */
use App\Apis\DirectSend\Email;
use Illuminate\Support\Carbon;

use App\Models\Channel\Channel;
use App\Models\Team\Team;
use App\Models\Team\InvitationCard;
use App\Models\Team\Member;

/**
 * 유저 모델 입니다.
 *
 * @author  dhtmdgkr123 <osh12201@gmail.com>
 * @version
 */
class User extends Authenticatable
{
    use Notifiable;
    use HasApiTokens;
    use MustVerifyEmail;

    /**
     * @var int 최대 채널 생성 갯수
     */
    public const DEFAULT_CREATE_CHANNEL_LIMIT_COUNT = 5;

    /**
     * @var int 팀 최대 생성 갯수
     */
    public const DEFAULT_CREATE_TEAM_LIMIT_COUNT = 3;

    /**
     * @var int 노티 안읽은 갯수 카운트 기본값
     */
    public const DEFAULT_UN_READ_BADGE_COUNT = 0;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nick_name', 'email', 'password',
        'is_policy_agree', 'is_privacy_agree',
        'profile_image'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    /**
     * 유저가 가진 채널들의 정보를 정의합니다.
     *
     * @var array
     */
    public static $channelsInfo = [
        'bannerImages:id,channel_id,banner_image',
        'broadcastAddress:channel_id,broadcast_address AS broadcastAddress,platform',
        'followers:user_id,nick_name AS nickName,email,profile_image AS profileImage'

    ];

    /**
     * 유저가 가진 팀들의 정보를 정의합니다.
     *
     * @var array
     */
    public $teamInfo = Team::TEAM_RELATIONS;

    protected $dates = ['deleted_at'];

    public function setPasswordAttribute(string $password): void
    {
        $this->attributes['password'] = bcrypt($password);
    }

    public function channels(): HasMany
    {
        return $this->hasMany(Channel::class, 'owner', 'id');
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class, 'owner', 'id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class, 'user_id', 'id');
    }

    public function invitationCards(): HasMany
    {
        return $this->hasMany(InvitationCard::class, 'user_id', 'id');
    }

    public function sendEmailVerificationNotification(): void
    {
        $sendInfo = [
            'receivers' => [
                ['email' => $this->email, 'name' => $this->nick_name]
            ],
            'subject' => __('emails.verify.title'),
            'view' => view('email.verify', [
                'userName' => $this->nick_name,
                'verifyUrl' => $this->verificationUrl($this)
            ])->render(),
        ];
        Email::send($sendInfo);
    }

    private function verificationUrl(User $user): string
    {
        $signedVerifyUrl = Url::temporarySignedRoute('verifyEmail', now()->addMinutes(config('auth.verification.expire', 60)), [
            'id' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ]);
        $parseSignedUrlResult = parse_url($signedVerifyUrl);
        $urlPath = explode('/api/v1/', $parseSignedUrlResult['path']);
        throw_if(($reqeustPath = data_get($urlPath, 1)) === null, new \InvalidArgumentException('cat not get signature'));

        $signedFrontVerifyUrl = trim(
            config('app.frontEndUrl') . $reqeustPath . '?' . $parseSignedUrlResult['query']
        );

        return $signedFrontVerifyUrl;
    }
}
