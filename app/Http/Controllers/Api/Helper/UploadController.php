<?php

namespace App\Http\Controllers\Api\Helper;

use App\Http\Controllers\Api\BaseController;
use App\Models\Helpers\UploadingHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UploadController extends BaseController
{

    /**
     * Загрузка фото на сервер
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'image|mimes:jpeg,png,jpg',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $path = UploadingHelper::uploadFile($request->image);

        return $this->sendResponse($path, 'Uploading success');
    }

    /**
     * Загрузка видео на сервер
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadVideo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'video' => 'required|mimetypes:video/x-ms-asf,video/x-flv,video/mp4,application/x-mpegURL,video/MP2T,video/3gpp,video/quicktime,video/x-msvideo,video/x-ms-wmv,video/avi',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $path = UploadingHelper::uploadFile($request->video);

        return $this->sendResponse($path, 'Uploading success');
    }
}
