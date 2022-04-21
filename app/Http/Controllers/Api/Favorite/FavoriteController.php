<?php

namespace App\Http\Controllers\Api\Favorite;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends BaseController
{
    /**
     * Добавить в избранное
     *
     * @param int $identify
     * @param Request $request
     * @return JsonResponse
     */
    public function add($identify, Request $request)
    {
        $user = $request->user();

        $user->addFavorite($identify);

        return $this->sendResponse($user->getFavoritesId(), 'Success');
    }

    /**
     * Список избранного
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function view(Request $request)
    {
        $user = $request->user();

        $favorites = $user->getFavorites();

        return $this->sendResponse($favorites, 'Success');
    }

    /**
     * Удаление из избранного
     *
     * @param int $identify
     * @param Request $request
     * @return JsonResponse
     */
    public function remove($identify, Request $request)
    {
        $user = $request->user();

        $user->removeFavorite($identify);

        return $this->sendResponse($user->getFavoritesId(), 'Success');
    }
}
