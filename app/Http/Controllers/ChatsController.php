<?php

namespace App\Http\Controllers;

use App\Actions\Chat\QuestionChatCreateAction;
use App\Http\Resources\Chat\MessageResource;
use App\Models\AdminUser;
use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show chats
     *
     */
    public function index(QuestionChatCreateAction $createAction)
    {
        $chats = Chat::where('type', Chat::TYPE_MY_QUESTION)->orderBy('updated_at', 'desc')->get();
        return view('chat.chats', ['chats' => $chats]);
    }

    /**
     * Show chat
     *
     */
    public function viewChat($chatId)
    {
        $chat = Chat::where('id', $chatId)->first();
        return view('chat.chat', ['chatId' => $chatId, 'chatName' => $chat->name]);
    }

    /**
     * Fetch all messages
     *
     */
    public function fetchMessages($chatId)
    {
        $chat = Chat::where('id', $chatId)->with(['messages' => function ($query) {
            $query->with('sender');
        }])->first();
//        dd($chat->messages);

        return response()->json(MessageResource::collection($chat->messages));
    }

    /**
     * Persist message to database
     *
     * @param $chatId
     * @param Request $request
     * @return string[]
     */
    public function sendMessage($chatId, Request $request)
    {
        $user = Auth::user();
        $chat = Chat::where('id', $chatId)->first();

        $message = $chat->messages()->create([
            'user_id' => ($user instanceof AdminUser) ? 0 : -1,
            'text' => $request->input('text')
        ]);

        //broadcast(new MessageSent($user, $message))->toOthers();

        return ['status' => 'Message Sent!'];
    }
}
