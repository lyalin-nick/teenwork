<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use App\Models\UserReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class UserController extends BaseController
{
    public function view($id, Request $request)
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

    public function report($id, Request $request)
    {
        $reporter = $request->user();
        $user = User::where('id', '=', $id)->first();

        if (!$user) {
            return $this->sendError('User not found');
        }

        if ($user->id == !$reporter->id) {
            return $this->sendError('You`re an idiot?');
        }

        $validator = Validator::make($request->all(), [
            'title_id' => 'required|integer',
            'title' => 'string',
            'text' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $report = UserReport::new($reporter->id, $user->id, $request->title_id, $request->title, $request->text);
        if (!$report) {
            return $this->sendError('Error report creating', [], 502);
        }
        return $this->sendResponse(['report_id' => $report->id], 'Report was create', 201);


    }
}
