<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\Response;

class AdafruitService
{
    public static function latest(): array
    {
        return Cache::remember('adafruit.latest.weight', 1, function () {

            $url = sprintf(
                'https://io.adafruit.com/api/v2/%s/feeds/%s',
                config('services.adafruit.username'),
                config('services.adafruit.feed')
            );

            /** @var Response $response */
            $response = Http::withHeaders([
                'X-AIO-Key' => config('services.adafruit.key'),
            ])->timeout(3)->get($url);

            if (! $response->successful()) {
                throw new \Exception('Gagal mengambil data dari Adafruit IO');
            }

            return [
                'value' => (float) $response->json('last_value'),
                'timestamp' => $response->json('updated_at'),
            ];
        });
    }
}