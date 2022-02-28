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

    public static function getCoordinates($place_id)
    {
        $clinet = new Client();

        $link = "https://maps.googleapis.com/maps/api/place/details/json?place_id={$place_id}&key=" . self::GOOGLE_API_KEY;

        $response = $clinet->request('GET', $link);

        $data = $response->getBody();
        $data = json_decode($data);

        return ['latitude' => $data['result']['geometry']['lat'], 'longitude' => $data['result']['geometry']['lng']];
    }
}
