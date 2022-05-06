<?php

namespace App\Http\Controllers\Api\Profile\Portfolio;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Portfolio\PortfolioLinkRequest;
use App\Models\PortfolioLink;
use Auth;
use Illuminate\Http\JsonResponse;

class PortfolioLinkController extends BaseController
{
    /**
     * Создание новой ссылки
     *
     * @param PortfolioLinkRequest $request
     * @return JsonResponse
     */
    public function store(PortfolioLinkRequest $request): JsonResponse
    {
        $user = Auth::user();
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
     * @param PortfolioLinkRequest $request
     * @return JsonResponse
     */
    public function edit(PortfolioLinkRequest $request, int $id): JsonResponse
    {
        $user = Auth::user();
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
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        $user = Auth::user();
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
