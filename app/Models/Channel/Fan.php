<?php

declare(strict_types=1);

namespace App\Models\Channel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 채널 좋아요 모델 클래스 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class Fan extends Model
{
    use SoftDeletes;

    /**
     * @var int 좋아요 권한 없음
     */
    public const AUTORIZE_FAIL = 1;

    /**
     * @var int 좋아요 가능
     */
    public const LIKE_OK = 2;

    /**
     * @var int 이미 좋아요 함
     */
    public const ALREADY_LIKE = 3;

    /**
     * @var int 좋아요 취소 성공
     */
    public const UNLIKE_OK = 4;

    /**
     * @var int 이미 좋아요 취소
     */
    public const ALREADY_UNLIKE = 5;

    protected $table = 'channel_fans';
    protected $fillable = [
        'channel_id',
        'user_id'
    ];
}
