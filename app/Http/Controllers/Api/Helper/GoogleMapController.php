<?php

namespace App\Http\Controllers\Api\Helper;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Helper\Google\AutocompleteRequest;
use App\Http\Requests\Api\Helper\Google\PlaceIdRequest;
use App\Models\Helpers\GoogleMap;
use Illuminate\Http\JsonResponse;

class GoogleMapController extends BaseController
{
    /**
     * Получение списка на автоподставление
     *
     * @param AutocompleteRequest $request
     * @return JsonResponse
     */
    public function autocomplete(AutocompleteRequest $request): JsonResponse
    {
        $autocomplete_results = GoogleMap::getAutocomplete($request->address);

        if (empty($autocomplete_results))
            return $this->sendError('Results not found');

        return $this->sendResponse($autocomplete_results, 'Results');
    }

    /**
     * Получение place_id по координатам
     *
     * @param PlaceIdRequest $request
     * @return JsonResponse
     */
    public function placeId(PlaceIdRequest $request): JsonResponse
    {
        $autocomplete_results = GoogleMap::getPlaceId($request->latitude, $request->longitude);

        if ($autocomplete_results === null)
            return $this->sendError('Results not found');

        return $this->sendResponse(['place_id' => $autocomplete_results], 'Results');
    }
}
