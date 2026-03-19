<?php

namespace Shared\Core\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalServiceClient
{
    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var string|null
     */
    protected $token;

    /**
     * ExternalServiceClient constructor.
     *
     * @param string $baseUrl
     * @param string|null $token
     */
    public function __construct(string $baseUrl, ?string $token = null)
    {
        $this->baseUrl = $baseUrl;
        $this->token = $token;
    }

    /**
     * Send a GET request
     *
     * @param string $endpoint
     * @param array $params
     * @return array|null
     */
    public function get(string $endpoint, array $params = []): ?array
    {
        return $this->request('get', $endpoint, $params);
    }

    /**
     * Send a POST request
     *
     * @param string $endpoint
     * @param array $data
     * @return array|null
     */
    public function post(string $endpoint, array $data = []): ?array
    {
        return $this->request('post', $endpoint, $data);
    }

    /**
     * Send a PUT request
     *
     * @param string $endpoint
     * @param array $data
     * @return array|null
     */
    public function put(string $endpoint, array $data = []): ?array
    {
        return $this->request('put', $endpoint, $data);
    }

    /**
     * Send a DELETE request
     *
     * @param string $endpoint
     * @return array|null
     */
    public function delete(string $endpoint): ?array
    {
        return $this->request('delete', $endpoint);
    }

    /**
     * Execute the HTTP request
     *
     * @param string $method
     * @param string $endpoint
     * @param array $data
     * @return array|null
     */
    protected function request(string $method, string $endpoint, array $data = []): ?array
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');

        $request = Http::timeout(10)->retry(3, 100);

        if ($this->token) {
            $request->withToken($this->token);
        }

        try {
            $response = $request->{$method}($url, $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("External service error [{$method} {$url}]: " . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error("External service exception [{$method} {$url}]: " . $e->getMessage());
            return null;
        }
    }
}
