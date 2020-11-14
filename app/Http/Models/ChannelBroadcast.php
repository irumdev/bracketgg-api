<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Factories\BroadcastFactory;

class ChannelBroadcast extends Model
{
    public static array $platforms = BroadcastFactory::PLATFORMS;
}
