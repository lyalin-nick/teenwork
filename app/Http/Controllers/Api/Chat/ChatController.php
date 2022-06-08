<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Chat\AddMessageRequest;
use App\Http\Resources\Chat\ChatResource;
use App\Http\Resources\Chat\MessageResource;
use App\Models\Chat;
use Auth;
use Illuminate\Http\JsonResponse;

class ChatController extends BaseController
{
    /**
     *
     * @return JsonResponse
     */
    public function chatsList(): JsonResponse
    {
        $user = Auth::user();
        $chats = $user
            ->chats()
            ->join('messages', function ($query) {
                $query->on('chats.last_message_id', '=', 'messages.id');
            })
            ->with(['users' => function ($query) use ($user) {
                $query->where('users.id', '!=', $user->id);
            }])
            ->select('chats.*',
                'messages.text as last_message',
                'messages.user_id as last_message_user_id',
                'messages.created_at as last_message_created_at',
                'chat_user.unread_messages_count as unread_messages_count')
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        $curPage = $chats->currentPage();
        $lastPage = $chats->lastPage();

        return $this->sendResponse([
            'currentPage' => $curPage,
            'lastPage' => $lastPage,
            'chats' => ChatResource::collection($chats->items())
        ], 'Success', 201);
    }


    public function fetchMessages($chatId): JsonResponse
    {
        $user = Auth::user();
        $chat = $user->chats()->where('id', $chatId)->first();

        if (!$chat) {
            return $this->sendError('Chat not found');
        }

        $messages = $chat->messages()
            ->orderBy('updated_at', 'desc')
            ->with('user')
            ->paginate(50);

        $curPage = $messages->currentPage();
        $lastPage = $messages->lastPage();

        if ($chat->type = Chat::TYPE_TASK) {
//            $offer = TaskOffer::where('task_id', $chat->identifier)->andWhere('user_id', )->first();
            $response_data = [
                'offer' => null,
                'currentPage' => $curPage,
                'lastPage' => $lastPage,
                'messages' => MessageResource::collection($messages->items())
            ];
        } else {
            $response_data = [
                'question' => null,
                'currentPage' => $curPage,
                'lastPage' => $lastPage,
                'messages' => MessageResource::collection($messages->items())
            ];
        }

        return $this->sendResponse($response_data, 'Success', 201);
    }

    public function addMessage($chatId, AddMessageRequest $request): JsonResponse
    {
        $user = Auth::user();
        $chat = $user->chats()->where('id', $chatId)->first();
        if (!$chat) {
            return $this->sendError('Chat not found');
        }

        $message = $chat->messages()->create([
            'user_id' => $user->id,
            'text' => $request->text,
            'img' => $request->img
        ]);

        return $this->sendResponse(['message_id' => $message->id], 'Message created', 201);
    }
}
