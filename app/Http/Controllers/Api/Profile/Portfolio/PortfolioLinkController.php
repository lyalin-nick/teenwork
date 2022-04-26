<?php

namespace App\Http\Controllers\Api\Profile\Portfolio;

use App\Http\Controllers\Api\BaseController;
use App\Models\PortfolioLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PortfolioLinkController extends BaseController
{
    /**
     * Создание новой ссылки
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'link' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = $request->user();
        $profile = $user->profile;

        $model = PortfolioLink::create(['profile_id' => $profile->id, 'link' => $request->link]);

        if (!$model) {
            return $this->sendError('Error creating');
        }

        return $this->sendResponse(['id' => $model->id], 'Create success');
    }

    /**
     * Обновление уже созданной ссылки
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function edit($id, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'link' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = $request->user();
        $profile = $user->profile;

        $link = $profile->portfolioLinks()->where('id', '=', $id)->first();


        if ($link) {
            if ($link->update($request->only('link'))) {
                return $this->sendResponse([], 'Update successful');
            }

            return $this->sendError('Update error');
        }

        return $this->sendError('Link not found');
    }

    /**
     * Удаление ссылки
     *
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function delete($id, Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->profile;

        $link = $profile->portfolioLinks()->where('id', '=', $id)->first();

        if ($link) {
            if ($link->delete()) {
                return $this->sendResponse($profile->getPortfolioLinksAsArray(), 'Deleted successful');
            }

            return $this->sendError('Deletion error');
        }

        return $this->sendError('Link not found');
    }
}
