<?php

namespace App\Http\Controllers\Api\Profile\Portfolio;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Portfolio\UploadImageRequest;
use App\Models\PortfolioImage;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PortfolioImageController extends BaseController
{
    /**
     * Загрузка и создание новых фото
     *
     * @param UploadImageRequest $request
     * @return JsonResponse
     */
    public function store(UploadImageRequest $request): JsonResponse
    {
        $user = Auth::user();
        $profile = $user->profile;

        $result = PortfolioImage::new($request->image, $profile->id, $request->description);

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
     * @return JsonResponse
     */
    public function delete($id): JsonResponse
    {
        $user = Auth::user();
        $profile = $user->profile;

        $photo = $profile->portfolioImages()->where('id', '=', $id)->first();

        if ($photo) {
            if ($photo->delete()) {
                return $this->sendResponse([], 'Delete successful');
            }

            return $this->sendError('Deletion error');
        }

        return $this->sendError('Photo not found');
    }
}
