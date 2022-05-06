<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\User\ReportRequest;
use App\Models\Review;
use App\Models\User;
use App\Models\UserReport;
use Auth;
use Illuminate\Http\JsonResponse;


class UserController extends BaseController
{
    /**
     * Просмотр профиля пользователя
     *
     * @param $id
     * @return JsonResponse
     */
    public function view($id): JsonResponse
    {
        $user = User::where('id', '=', $id)->with('profile')->first();
        if ($user) {
            $profile = $user->profile;
            $user_data = [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'photo_preview' => $profile->getProfilePreviewImageLink(),
                'photo' => $profile->getProfileImageLink(),
                'number_performer_tasks' => $profile->number_performer_tasks,
                'number_employer_tasks' => $profile->number_employer_tasks,
                'status' => $profile->status,
                'verified' => $user->isVerified(),
                'about' => $profile->about,
                'video' => $profile->getProfileVideoLink(),
                'portfolio' => $profile->getPortfolio(),//$user->isPerformer() ? $profile->getPortfolio() : null,
                'rating' => $profile->rating,
                'stars' => $user->getStars(),//$user->isPerformer() ? $user->getStars() : null,
                'last_review' => $user->getLastReview(),
                'created_at' => date('Y-m-d', strtotime($user->created_at))
            ];

            return $this->sendResponse($user_data, 'User');

        }

        return $this->sendError('User not found');
    }

    /**
     * Отправка жалобы на пользователя
     *
     * @param $id
     * @param ReportRequest $request
     * @return JsonResponse
     */
    public function report(ReportRequest $request, $id): JsonResponse
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
}
