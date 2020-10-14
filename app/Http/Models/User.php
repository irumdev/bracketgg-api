<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\URL;
use App\Apis\DirectSend\Email;

use App\Models\Channel;

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
        'bannerImages:channel_id,banner_image',
        'broadcastAddress:channel_id,broadcast_address AS broadcastAddress,platform',
        'followers:user_id,nick_name AS nickName,email,profile_image AS profileImage'

    ];

    protected $dates = ['deleted_at'];

    public function setPasswordAttribute(string $password): void
    {
        $this->attributes['password'] = bcrypt($password);
    }

    public function channels(): HasMany
    {
        return $this->hasMany(Channel::class, 'owner', 'id');
    }

    public function sendEmailVerificationNotification(): void
    {
        Email::send([
            'receivers' => [
                ['email' => $this->email, 'name' => $this->nick_name]
            ],
            'subject' => __('emails.verify.title'),
            'view' => view('email.verify', [
                'userName' => $this->nick_name,
                'verifyUrl' => URL::signedRoute('verifyEmail', [
                    'id' => $this->id,
                    'hash' => sha1($this->email),
                ])
            ])->render()
        ]);

    }

    private function getVerifyUrl()
    {
        # code...
    }
}
