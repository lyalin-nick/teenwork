<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\User\ReportRequest;
use App\Http\Resources\Profile\ViewResource;
use App\Models\Review;
use App\Models\User;
use App\Models\UserReport;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;


class UserController extends BaseController
{
    /**
     * Просмотр профиля пользователя
     *
     * @param int $id
     * @return JsonResponse
     */
    public function view(int $id): JsonResponse
    {
        $user = User::where('id', '=', $id)->with('profile')->first();
        if ($user) {
            return $this->sendResponse(new ViewResource($user), 'User');
        }

        return $this->sendError('User not found');
    }

    /**
     * Отправка жалобы на пользователя
     *
     * @param int $id
     * @param ReportRequest $request
     * @return JsonResponse
     */
    public function report(ReportRequest $request, int $id): JsonResponse
    {
        $reporter = Auth::user();
        $user = User::where('id', '=', $id)->first();

        if (!$user) {
            return $this->sendError('User not found');
        }

        if ($user->id !== $reporter->id) {
            return $this->sendError('You`re an idiot?');
        }

        $report = UserReport::new($reporter->id, $user->id, $request->title_id, $request->title, $request->text);
        if (!$report) {
            return $this->sendError('Error report creating', [], 502);
        }
        return $this->sendResponse(['report_id' => $report->id], 'Report was create', 201);


    }

    /**
     * Просмотр отзывов о пользователе
     *
     * @param $id
     * @return JsonResponse
     */
    public function reviews($id): JsonResponse
    {
        $reviews_data = Review::search($id);
        return $this->sendResponse($reviews_data, 'Reviews');
    }

    /**
     * Просмотр отзывов о пользователе
     *
     * @param $id
     * @return JsonResponse
     */
    public function reviewsCount($id, Request $request): JsonResponse
    {
        $user = User::where('id', $id)->first();
        if (!$user) {
            return $this->sendError('User not found', 404);
        }

        return $this->sendResponse($user->getStars($request->all()), 'Reviews');
    }
}
