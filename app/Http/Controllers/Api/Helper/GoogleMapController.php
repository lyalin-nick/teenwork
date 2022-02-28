<?php

namespace App\Http\Controllers\Api\Helper;

use App\Http\Controllers\Api\BaseController;
use App\Models\Helpers\GoogleMap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GoogleMapController extends BaseController
{
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
            return $this->sendError([], 'Results not found');

        return $this->sendResponse($autocomplete_results, 'Results');
    }
}
