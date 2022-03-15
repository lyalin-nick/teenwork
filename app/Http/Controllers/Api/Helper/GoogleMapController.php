<?php

namespace App\Http\Controllers\Api\Helper;

use App\Http\Controllers\Api\BaseController;
use App\Models\Helpers\GoogleMap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GoogleMapController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/helper/autocomplete",
     *     operationId="helperAutocomplete",
     *     summary="Автодополнение для строки ввода адреса",
     *     @OA\Parameter (
     *         name="address",
     *         in="query",
     *         description="Вводимая строка в поле поимка",
     *         example="Bullhead City, AZ 86442",
     *         required=true,
     *         @OA\Schema (
     *              type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *     )
     * )
     */
    public function autocomplete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $autocomplete_results = GoogleMap::getAutocomplete($request->address);

        if (empty($autocomplete_results))
            return $this->sendError('Results not found');

        return $this->sendResponse($autocomplete_results, 'Results');
    }

    public function placeId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|string',
            'longitude' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $autocomplete_results = GoogleMap::getPlaceId($request->latitude, $request->longitude);

        if ($autocomplete_results === null)
            return $this->sendError('Results not found');

        return $this->sendResponse(['place_id' => $autocomplete_results], 'Results');
    }
}
