<?php

namespace App\Http\Controllers\Api\Chat;

use App\Actions\File\FileUploadAction;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Chat\SendMessageRequest;
use App\Http\Requests\Api\Helper\UploadFile\ImageRequest;
use App\Http\Resources\Chat\ChatResource;
use App\Http\Resources\Chat\MessageResource;
use App\Models\Chat;
use App\Models\MyQuestion;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChatController extends BaseController
{
    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function chatsList(Request $request): JsonResponse
    {
        $user = Auth::user();

        $chats = $user
            ->chats();
        if (isset($request->status)) {
            $chats->where(['status' => $request->status]);
        }
        $chats = $chats
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

    /**
     *
     * @return JsonResponse
     */
    public function countChats(): JsonResponse
    {
        $user = Auth::user();

        return $this->sendResponse(['count' => $user->getCountChats()], 'Success', 201);
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
            ->with('sender')
            ->with(['messageStatuses' => function ($query) use ($user) {
                $query->where('user_id', '!=', $user->id);
            }])
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
            $question = MyQuestion::where('id', $chat->identifier)->andWhere('user_id', $user->id)->first();
            $response_data = [
                'question' => isset($question) ? $question->id : null,
                'currentPage' => $curPage,
                'lastPage' => $lastPage,
                'messages' => MessageResource::collection($messages->items())
            ];
        }

        return $this->sendResponse($response_data, 'Success', 201);
    }

    public function sendMessage($chatId, SendMessageRequest $request): JsonResponse
    {
        $user = Auth::user();
        $chat = $user->chats()->where('id', $chatId)->first();
        if (!$chat) {
            return $this->sendError('Chat not found');
        }

        $message = $chat->messages()->create([
            'user_id' => $user->id,
            'text' => $request->get('text'),
            'images' => $request->get('images')
        ]);

        return $this->sendResponse(['message_id' => $message->id], 'Message created', 201);
    }

    public function sendImage($chatId, ImageRequest $request, FileUploadAction $uploadAction): JsonResponse
    {
        $user = Auth::user();
        $chat = $user->chats()->where('id', $chatId)->first();
        if (!$chat) {
            return $this->sendError('Chat not found');
        }

        $file_path = $uploadAction($request->image, "chat/{$chatId}");
        if (!$file_path) {
            return $this->sendError('Image uploading error', [], 500);
        }

        return $this->sendResponse(asset(Storage::url($file_path)), 'Message created', 201);
    }

    public function readingMessages($id, Request $request): JsonResponse
    {
        $user = Auth::user();

        $reading_messages = $request->get('messages');
        $chat = Chat::where('id', $id)->first();
        if (!$chat) {
            return $this->sendError('Chat not found');
        }

        $messages = $chat->messages()
            ->whereIn('id', $reading_messages)
            ->get();

        if (!$messages) {
            return $this->sendError('Messages not found');
        }

        foreach ($messages as $message) {
            $message->messageStatuses()->where('user_id', $user->id)
                ->update(['reading' => true]);
        }

        return $this->sendResponse([], 'Updating success');
    }
}
