<?php

declare(strict_types=1);

namespace App\Models\Channel;

use Illuminate\Database\Eloquent\Model;
use App\Factories\BroadcastFactory;

/**
 * 채널 방송국 클래스 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class Broadcast extends Model
{
    protected $table = 'channel_broadcasts';
    public static array $platforms = BroadcastFactory::PLATFORMS;
}
