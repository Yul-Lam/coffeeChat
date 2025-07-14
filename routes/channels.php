<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{senderId}.{receiverId}', function ($user, $senderId, $receiverId) {
    return $user->id === $senderId || $user->id === $receiverId;
});
