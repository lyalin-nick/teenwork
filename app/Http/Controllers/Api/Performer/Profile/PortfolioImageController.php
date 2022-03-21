<?php

namespace App\Http\Controllers\Api\Performer\Profile;

use App\Http\Controllers\Api\BaseController;
use App\Models\PortfolioImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PortfolioImageController extends BaseController
{
    /**
     * Загрузка и создание новых фото
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = $request->user();
        $profile = $user->profile;

        $result = PortfolioImage::createModel($request->image, $profile->id, $request->description);

        if (!$result) {
            Log::error("Portfolio photo error");

            return $this->sendError('Error uploading');
        }

        return $this->sendResponse([], 'Upload success');
    }

    /**
     * Удаление фото
     *
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function delete($id, Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->profile;

        $photo = $profile->portfolioImages()->where('id', $id)->first();

        if ($photo) {
            if ($photo->delete()) {
                return $this->sendResponse([], 'Delete successful');
            }

            return $this->sendError('Deletion error');
        }

        return $this->sendError('Photo not found');
    }
}
