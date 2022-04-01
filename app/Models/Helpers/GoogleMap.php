<?php

namespace App\Models\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class GoogleMap
{
    /**
     * Получение мест для автокомплита выбора адреса
     * @param $input
     * @return array
     * @throws GuzzleException
     */
    public static function getAutocomplete($input): array
    {
        $key = config('services.google_api.key');

        $client = new Client();

        $link = "https://maps.googleapis.com/maps/api/place/autocomplete/json?input={$input}&types=address&key={$key}";

        $response = $client->request('GET', $link);

        $data = $response->getBody();
        $data = json_decode($data);

        $autocomplete = [];
        foreach ($data->predictions as $prediction) {
            $autocomplete[] = ['address' => $prediction->description, 'place_id' => $prediction->place_id];
        }

        return $autocomplete;
    }

    /**
     * Получить placeId места из ГуглКарт по координатам
     *
     * @param $latitude
     * @param $longitude
     * @return string|null
     * @throws GuzzleException
     */
    public static function getPlaceId($latitude, $longitude)
    {
        $key = config('services.google_api.key');

        $client = new Client();

        $link = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$latitude},{$longitude}&key={$key}";

        $response = $client->request('GET', $link);

        $data = $response->getBody();
        $data = json_decode($data);

        return !empty($data->results) ? $data->results[0]->place_id : null;
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

        $response = $client->request('GET', $link);

        $data = $response->getBody();
        $data = json_decode($data);

        if (!empty($data->result))
            return [
                'latitude' => $data->result->geometry->location->lat,
                'longitude' => $data->result->geometry->location->lng
            ];

        return null;
    }
}
