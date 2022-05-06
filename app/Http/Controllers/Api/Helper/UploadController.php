<?php

namespace App\Http\Controllers\Api\Helper;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Helper\UploadFile\ImageRequest;
use App\Http\Requests\Api\Helper\UploadFile\VideoRequest;
use App\Models\Helpers\UploadingHelper;
use Illuminate\Http\JsonResponse;

class UploadController extends BaseController
{
    /**
     * Загрузка фото на сервер
     *
     * @param ImageRequest $request
     * @return JsonResponse
     */
    public function uploadImage(ImageRequest $request): JsonResponse
    {
        $path = UploadingHelper::uploadFile($request->image);

        return $this->sendResponse($path, 'Uploading success');
    }

    /**
     * Загрузка видео на сервер
     *
     * @param VideoRequest $request
     * @return JsonResponse
     */
    public function uploadVideo(VideoRequest $request): JsonResponse
    {
        $path = UploadingHelper::uploadFile($request->video);

        return $this->sendResponse($path, 'Uploading success');
    }
}
