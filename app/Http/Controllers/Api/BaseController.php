<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="TeenWork",
 *      description="API для мобильного приложения TeenWork",
 *      @OA\Contact(
 *          url="https://t.me/nikitalyalin"
 *      )
 * )
 * @OA\Tag (
 *     name="Auth",
 *     description="Методы для авторизации"
 * )
 * @OA\Tag (
 *     name="Employer-Task",
 *     description="Методы для управления задачами для роли заказчик"
 * )
 * @OA\Tag (
 *     name="Employer-Profile",
 *     description="Методы для управления профилем для роли заказчик"
 * )
 * @OA\Tag (
 *     name="Performer-Profile",
 *     description="Методы для управления профилем для роли исполнитель"
 * )
 * @OA\Tag (
 *     name="Profile",
 *     description="Общие методы для управления профилем"
 * )
 * @OA\Tag (
 *     name="Portfolio",
 *     description="Методы для управления портфолио"
 * )
 * @OA\Tag (
 *     name="Dictionary",
 *     description="Словарь"
 * )
 * @OA\Tag (
 *     name="Helpers",
 *     description="Хелперы"
 * )
 * @OA\Server (
 *     url="http://teenwork/api/",
 *     description="Локальный серв"
 * )
 * @OA\Server (
 *     url="http://teenwork.demo.onlinebees.ru/api/",
 *     description="Рабочий серв"
 * )
 * @OA\SecurityScheme (
 *     securityScheme="Bearer",
 *     type="apiKey",
 *     name="Authorization",
 *     in="header"
 *)
 */
class BaseController extends Controller
{
    public function sendResponse($result, $message, $code = 200)
    {
        $response = [
            'success' => true,
            'data' => $result,
            'message' => $message
        ];
        return response()->json($response, $code);
    }

    public function sendError($error, $errorMessages = [], $code = 404)
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


    /*
    public function uploadImages($images, $class_name, $parent_model = null, $foreign_field = null): \Illuminate\Http\JsonResponse
    {
        try {
            if ($images) {
                $images_response_data = [];
                foreach ($images as $pos => $image) {

                    $path = 'uploads/' . strtolower(class_basename($class_name));
                    $path .= ($parent_model) ? $parent_model->id : '';

                    $img_path = $image->store($path, 'public');
                    if ($img_path) {
                        $path_info = pathinfo(asset('/storage/' . $img_path));
                        $image_data = [
                            'name' => $path_info['filename'],
                            'ext' => $path_info['extension'],
                            'path' => 'storage' . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR
                        ];
                        if ($parent_model) {
                            $foreign_field = $foreign_field ?: strtolower(class_basename(get_class($parent_model))) . '_id';

                            $image_data[$foreign_field] = $parent_model->id;
                            $image_data['alt'] = $parent_model->name . ' - ' . ($pos + 1);
                        }
                        $image = $class_name::create($image_data);
                        $images_response_data['images'][] = $image->id;
                    }
                }
                return $this->sendResponse($images_response_data, 'Upload successful');
            }
            return $this->sendError('Images is empty');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }
    */
}
