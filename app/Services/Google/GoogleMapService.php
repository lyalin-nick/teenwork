<?php

namespace App\Services\Google;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class GoogleMapService
{
    private $key;

    public function __construct()
    {
        $this->key = config('services.google_api.key');
    }

    public function autocomplete($input): array
    {
        $client = new Client();

        $link = "https://maps.googleapis.com/maps/api/place/autocomplete/json?input={$input}&types=address&key={$this->key}";

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

    public function placeId($latitude, $longitude)
    {
        $client = new Client();

        $link = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$latitude},{$longitude}&key={$this->key}";
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

    public function coords($place_id)
    {
        $client = new Client();

        $link = "https://maps.googleapis.com/maps/api/place/details/json?place_id={$place_id}&key={$this->key}";

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
