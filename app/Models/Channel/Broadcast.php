<?php

declare(strict_types=1);

namespace App\Models\Channel;

use Illuminate\Database\Eloquent\Model;
use App\Factories\BroadcastFactory;

class Broadcast extends Model
{
    protected $table = 'channel_broadcasts';
    public static array $platforms = BroadcastFactory::PLATFORMS;
}
