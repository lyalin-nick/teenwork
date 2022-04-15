<?php

namespace App\Models\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class GoogleMap
{
    /**
     * @param $input
     * @return array
     */
    public static function getAutocomplete($input): array
    {
        $key = config('services.google_api.key');

        $client = new Client();

        $link = "https://maps.googleapis.com/maps/api/place/autocomplete/json?input={$input}&types=address&key={$key}";

        $autocomplete = [];
        try {
            $response = $client->request('GET', $link);

            $data = $response->getBody();
            $data = json_decode($data);
            if (isset($data->predictions)) {
                foreach ($data->predictions as $prediction) {
                    $autocomplete[] = ['address' => $prediction->description, 'place_id' => $prediction->place_id];
                }
            }
        } catch (GuzzleException $e) {
            Log::error($e->getMessage());
        }

        return $autocomplete;
    }

    /**
     * Получить placeId места из ГуглКарт по координатам
     *
     * @param $latitude
     * @param $longitude
     * @return string|null
     */
    public static function getPlaceId($latitude, $longitude)
    {
        $key = config('services.google_api.key');

        $client = new Client();

        $link = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$latitude},{$longitude}&key={$key}";
        try {
            $response = $client->request('GET', $link);
            $data = $response->getBody();
            $data = json_decode($data);

            return !empty($data->results) ? $data->results[0]->place_id : null;

        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());
        }
        return null;
    }

    /**
     * Not used
     *
     * @param $place_id
     * @return array|null
     * @throws GuzzleException
     */
    public static function getCoordinates($place_id)
    {
        $key = config('services.google_api.key');

        $client = new Client();

        $link = "https://maps.googleapis.com/maps/api/place/details/json?place_id={$place_id}&key={$key}";

        try {
            $response = $client->request('GET', $link);

            $data = $response->getBody();
            $data = json_decode($data);

            if (!empty($data->result))
                return [
                    'lat' => $data->result->geometry->location->lat,
                    'lng' => $data->result->geometry->location->lng
                ];
        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());
        }

        return null;
    }
}
