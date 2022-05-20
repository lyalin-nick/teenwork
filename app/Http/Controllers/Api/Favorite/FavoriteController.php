<?php

namespace App\Http\Controllers\Api\Favorite;

use App\Actions\Favorite\AddFavoriteAction;
use App\Actions\Favorite\DeleteFavoriteAction;
use App\Actions\Favorite\ListFavoriteAction;
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
    public function add(int $identify, AddFavoriteAction $addFavoriteAction): JsonResponse
    {
        $user = Auth::user();

        $added = $addFavoriteAction($user, $identify);

        return ($added) ?
            $this->sendResponse($user->getFavoritesId(), 'Success') :
            $this->sendError('Error! Item didnt added', 501);
    }

    /**
     * Список избранного
     *
     * @return JsonResponse
     */
    public function view(ListFavoriteAction $listFavoriteAction): JsonResponse
    {
        $user = Auth::user();

        return $this->sendResponse($listFavoriteAction($user), 'Success');
    }

    /**
     * Удаление из избранного
     *
     * @param int $identify
     * @return JsonResponse
     */
    public function remove(int $identify, DeleteFavoriteAction $deleteFavoriteAction): JsonResponse
    {
        $user = Auth::user();

        $deleteFavoriteAction($user, $identify);

        return $this->sendResponse($user->getFavoritesId(), 'Success');
    }
}
