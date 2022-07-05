<?php

namespace App\Http\Controllers\Api\Chat;

use App\Actions\File\FileUploadAction;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Chat\NewChatRequest;
use App\Http\Requests\Api\Chat\SendMessageRequest;
use App\Http\Requests\Api\Helper\UploadFile\ImageRequest;
use App\Http\Resources\Chat\ChatResource;
use App\Http\Resources\Chat\MessageResource;
use App\Models\Chat;
use App\Models\MyQuestion;
use App\Models\Task;
use App\Models\TaskOffer;
use App\Models\User;
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

        $chats = $user->chats();
        if (isset($request->status)) {
            $chats->where(['status' => $request->status]);
        }

        $chats = $chats
            ->with(['users' => function ($query) use ($user) {
                $query->where('users.id', '!=', $user->id);
            }, 'lastMessage'])
            ->select('chats.*', 'chat_user.unread_messages_count as unread_messages_count')
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


    /**
     * Получение сообщений чата
     * @param int $chatId ID чата
     * @return JsonResponse
     */
    public function fetchMessages(int $chatId): JsonResponse
    {
        $user = Auth::user();
        $chat = $user->chats()->where('id', $chatId)->first();

        if (!$chat) {
            return $this->sendError('Chat not found');
        }

        $messages = $chat->messages()
            ->orderBy('updated_at', 'desc')
            ->with('sender', 'taskOffer')
            ->with(['messageStatuses' => function ($query) use ($user) {
                $query->where('user_id', '=', $user->id);
            }])
            ->paginate(30);
        $curPage = $messages->currentPage();
        $lastPage = $messages->lastPage();

        if ($chat->type = Chat::TYPE_TASK) {
//            $offer = TaskOffer::where('task_id', $chat->identifier)->andWhere('user_id', )->first();
            $response_data = [
                'offer' => ($chat->users()->count() == 2) ? TaskOffer::where('chat_id', $chatId)->exists() : null,
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

    /**
     * Отправка сообщения в чат
     * @param int $chatId ID чата
     * @param SendMessageRequest $request
     * @return JsonResponse
     */
    public function sendMessage(int $chatId, SendMessageRequest $request): JsonResponse
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

    /**
     * Загрузка файлов в чат
     * @param int $chatId ID чата
     * @param ImageRequest $request запрос с файлами
     * @param FileUploadAction $uploadAction
     * @return JsonResponse
     */
    public function sendImage(int $chatId, ImageRequest $request, FileUploadAction $uploadAction): JsonResponse
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

    /**
     * Изменение статуса сообщения на "прочитано"
     * @param int $chatId ID чата
     * @param Request $request
     * @return JsonResponse
     */
    public function readingMessages(int $chatId, Request $request): JsonResponse
    {
        $user = Auth::user();

        $reading_messages = $request->get('messages');
        $chat = Chat::where('id', $chatId)->first();
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
            $message->messageStatuses()
                ->where('user_id', $user->id)
                ->updateOrCreate(['user_id' => $user->id, 'reading' => true]);
        }

        $user->refreshUnreadMessagesCounter($chat->id);

        return $this->sendResponse([], 'Updating success');
    }

    /**
     * Поиск или создания чата с исполнителем по задаче
     *
     * @param NewChatRequest $request
     * @return JsonResponse
     */
    public function findOrNewChat(NewChatRequest $request): JsonResponse
    {
        $user = Auth::user();
        $task = Task::where('id', $request->task_id)->first();
        if (!$task) {
            return $this->sendError('Task not found');
        }
        $performer = User::where('id', $request->performer_id)->first();
        if (!$performer) {
            return $this->sendError('User not found');
        }

        $chat = Chat::where('type', '=', Chat::TYPE_TASK)
            ->where('identifier', '=', $task->id)
            ->select('chats.*')
            ->whereRaw("(SELECT COUNT(*) FROM chat_user WHERE chats.id=chat_user.chat_id)=(SELECT COUNT(*) FROM chat_user WHERE chats.id=chat_user.chat_id AND chat_user.user_id IN ({$user->id}, {$performer->id}))")
            ->first();

        if (!$chat) {
            $chat = Chat::create([
                'type' => Chat::TYPE_TASK,
                'identifier' => $task->id,
                'name' => $task->name
            ]);

            if (!$chat) {
                return $this->sendError('Error creating chat', [], 500);
            }

            $chat->users()->attach($user);
            $chat->users()->attach($performer);
        }


        return $this->sendResponse(['chat_id' => $chat->id], 'Chat finding');
    }
}
