<?php

namespace App\Http\Controllers\Api\Helper;

use App\Actions\File\FileUploadAction;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Helper\UploadFile\ImageRequest;
use App\Http\Requests\Api\Helper\UploadFile\VideoRequest;
use Illuminate\Http\JsonResponse;

class UploadController extends BaseController
{
    /**
     * Загрузка фото на сервер
     *
     * @param ImageRequest $request
     * @param FileUploadAction $uploadAction
     * @return JsonResponse
     */
    public function uploadImage(ImageRequest $request, FileUploadAction $uploadAction): JsonResponse
    {
        $path = $uploadAction($request->image);

        return $this->sendResponse($path, 'Uploading success');
    }

    /**
     * Загрузка видео на сервер
     *
     * @param VideoRequest $request
     * @param FileUploadAction $uploadAction
     * @return JsonResponse
     */
    public function uploadVideo(VideoRequest $request, FileUploadAction $uploadAction): JsonResponse
    {
        $path = $uploadAction($request->video);

        return $this->sendResponse($path, 'Uploading success');
    }
}
