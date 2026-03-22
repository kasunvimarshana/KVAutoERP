<?php

namespace kasunvimarshana\WeatherForecast;

class WeatherService
{
    /**
     * Get the weather forecast for the specified city.
     * Returns mock data for demonstration.
     *
     * @param string|null $city
     * @return array
     */
    public function getForecastByCity(?string $city = null): array
    {
        // Take city input by the user
        if (!$city) {
            $city = config('weatherforecast.default_city');
        }

        // Retrieve API token from config
        $apiToken = config('weatherforecast.api_token');

        // Call the mock weather API with authentication header
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiToken}",
        ])->get("https://api.mockweather.com/forecast?city={$city}");

        // Return the response data
        return $response->json();
    }
}
