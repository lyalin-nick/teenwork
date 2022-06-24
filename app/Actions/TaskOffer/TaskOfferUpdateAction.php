<?php

namespace App\Actions\TaskOffer;

use App\Models\Chat;
use App\Models\TaskOffer;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class TaskOfferUpdateAction
{
    /**
     * @param TaskOffer $taskOffer
     * @param User $performer
     * @param bool $accept
     * @param string|null $message
     * @return JsonResponse
     */
    public function __invoke(TaskOffer $taskOffer, User $performer, bool $accept, string $message = null): JsonResponse
    {
        $task = $taskOffer->task;
        $taskOfferChat = $taskOffer->chat;

        if ($taskOffer->accept === null) { // первый ответ на оффер
            if ($task->acceptedTaskOffers()->count() >= $task->amount_of_workers) {
                return $this->sendError('Performers for this task are already assigned', [], 405);
            }

            $taskOffer->accept = $accept;

            if (!$taskOffer->save()) {
                return $this->sendError('Error accepted offer', [], 502);
            }

            if ($taskOffer->accept) { // если исполнитель принял оффер

                if ($task->chat_id === null) { // если у задачи еще нет общего чата

                    if ($task->amount_of_workers == 1) { // если у задачи один исполнитель, то оставляем текущий чат как основной для задачи
                        $task->chat_id = $taskOffer->chat_id;
                    } else {
                        $chat = Chat::create([
                            'type' => Chat::TYPE_TASK,
                            'identifier' => $task->id,
                            'name' => $task->name,
                        ]);
                        $task->chat_id = $chat->id;
                        $taskOfferChat->status = Chat::STATUS_HISTORY;
                        $taskOfferChat->save();
                    }

                    if ($task->save()) {
                        $task->refresh();
                        $chat = $task->chat;
                        if ($chat) {
                            $chat->users()->attach($task->user);
                            $chat->users()->attach($taskOffer->user);
                        }
                    }
                } else { // если общий чат существует, то добавляем исполнителя в него
                    $chat = $task->chat;
                    $chat->users()->attach($taskOffer->user);
                }

                return $this->sendResponse(['chat' => $task->chat->id], 'Create successful', 201);
            }
            $taskOfferChat->status = Chat::STATUS_HISTORY;
            $taskOfferChat->save();

            return $this->sendResponse(['chat' => $taskOffer->chat_id], 'Create successful', 201);

        } elseif ($taskOffer->accept === true) { // отклонение ранее принятого оффера
            $taskOffer->accept = $accept;

            if (!$taskOffer->save()) {
                return $this->sendError('Error declined offer', [], 502);
            }

            if (!empty($message)) {
                $taskOfferChat->messages()->create([
                    'user_id' => $performer->id,
                    'text' => $message
                ]);
            }

            $taskOfferChat->status = Chat::STATUS_HISTORY;
            $taskOfferChat->save();

            $taskChat = $taskOffer->task->chat;

            $taskChat->users()->detach($performer); // удалим пользователя из общего чата
        }
        return $this->sendError('You have already rejected this offer!', [], 405);
    }

    public function sendResponse($result, $message, $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'data' => $result,
            'message' => $message
        ];
        return response()->json($response, $code);
    }

    public function sendError($error, $errorMessages = [], $code = 404): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $error
        ];
        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}
