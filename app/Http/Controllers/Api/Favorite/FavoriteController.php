<?php

namespace App\Http\Controllers\Api\Favorite;

use App\Http\Controllers\Api\BaseController;
use Auth;
use Illuminate\Http\JsonResponse;

class FavoriteController extends BaseController
{
    /**
     * Добавить в избранное
     *
     * @param int $identify
     * @return JsonResponse
     */
    public function add(int $identify): JsonResponse
    {
        $user = Auth::user();

        $user->addFavorite($identify);

        return $this->sendResponse($user->getFavoritesId(), 'Success');
    }

    /**
     * Список избранного
     *
     * @return JsonResponse
     */
    public function view(): JsonResponse
    {
        $user = Auth::user();

        $favorites = $user->getFavorites();

        return $this->sendResponse($favorites, 'Success');
    }

    /**
     * Удаление из избранного
     *
     * @param int $identify
     * @return JsonResponse
     */
    public function remove(int $identify): JsonResponse
    {
        $user = Auth::user();

        $user->removeFavorite($identify);

        return $this->sendResponse($user->getFavoritesId(), 'Success');
    }
}
