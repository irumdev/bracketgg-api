<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationMessage extends Model
{
    public const REJECT_ACCEPT_INVITE_TEAM = 1;

    use HasFactory;

    protected $table = 'notification_messages';
    protected $fillable = [
        'type',
        'message',
        'user_id',
        'is_read',
        'is_receive',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_receive' => 'boolean',
        'message' => 'array'
    ];
}
