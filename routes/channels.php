<?php

use App\Models\AdminUser;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int)$user->id === (int)$id;
});
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = $user->chats()->where('id', $chatId)->first();
    if (!$chat)
        return false;

    return ($user instanceof AdminUser && $chat->type == Chat::TYPE_MY_QUESTION) || ($user instanceof User);
});
Broadcast::channel('chatlist.{userId}', function ($user, $userId) {
    return $user->id === (int)$userId;
});
