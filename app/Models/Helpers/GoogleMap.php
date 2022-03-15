<?php

namespace App\Models\Helpers;

use GuzzleHttp\Client;

class GoogleMap
{
    const GOOGLE_API_KEY = "AIzaSyCBtiB9qc9100FDxgoeeq2F6kpBea2HOuQ";

    public static function getAutocomplete($input)
    {
        $clinet = new Client();

        $link = "https://maps.googleapis.com/maps/api/place/autocomplete/json?input={$input}&types=address&key=" . self::GOOGLE_API_KEY;

        $response = $clinet->request('GET', $link);

        $data = $response->getBody();
        $data = json_decode($data);

        $autocomplete = [];
        foreach ($data->predictions as $prediction) {
            $autocomplete[] = ['address' => $prediction->description, 'place_id' => $prediction->place_id];
        }

        return $autocomplete;
    }

    /**
     * Not used
     *
     * @param $place_id
     * @return array|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function getCoordinates($place_id)
    {
        $clinet = new Client();

        $link = "https://maps.googleapis.com/maps/api/place/details/json?place_id={$place_id}&key=" . self::GOOGLE_API_KEY;

        $response = $clinet->request('GET', $link);

        $data = $response->getBody();
        $data = json_decode($data);

        if (!empty($data->result))
            return [
                'latitude' => $data->result->geometry->location->lat,
                'longitude' => $data->result->geometry->location->lng
            ];

        return null;
    }

    /**
     * Получить placeId места из ГуглКарт
     *
     * @param $latitude
     * @param $longitude
     * @return mixed|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function getPlaceId($latitude, $longitude)
    {
        $clinet = new Client();

        $link = "https://maps.googleapis.com/maps/api/geocode/json? ={$latitude},{$longitude}&key=" . self::GOOGLE_API_KEY;

        $response = $clinet->request('GET', $link);

        $data = $response->getBody();
        $data = json_decode($data);
        dd($data);
        return !empty($data->results) ? $data->results[0]['placeId'] : null;
    }
}
