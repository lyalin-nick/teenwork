<?php

namespace App\Http\Controllers\Api\Helper;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Helper\Google\AutocompleteRequest;
use App\Http\Requests\Api\Helper\Google\PlaceIdRequest;
use App\Services\Google\GoogleMapService;
use Illuminate\Http\JsonResponse;

class GoogleMapController extends BaseController
{
    /**
     * Получение списка на автоподставление
     *
     * @param AutocompleteRequest $request
     * @param GoogleMapService $service
     * @return JsonResponse
     */
    public function autocomplete(AutocompleteRequest $request, GoogleMapService $service): JsonResponse
    {
        $autocomplete_results = $service->autocomplete($request->address);

        if (empty($autocomplete_results))
            return $this->sendError('Results not found');

        return $this->sendResponse($autocomplete_results, 'Results');
    }

    /**
     * Получение place_id по координатам
     *
     * @param PlaceIdRequest $request
     * @param GoogleMapService $service
     * @return JsonResponse
     */
    public function placeId(PlaceIdRequest $request, GoogleMapService $service): JsonResponse
    {
        $autocomplete_results = $service->placeId($request->latitude, $request->longitude);

        if ($autocomplete_results === null)
            return $this->sendError('Results not found');

        return $this->sendResponse(['place_id' => $autocomplete_results], 'Results');
    }
}
