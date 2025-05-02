<?php

namespace App\Services\Innovation;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
require_once app_path('Libraries/nusoap.php');

class InnovationService
{
    protected $client;
    protected $user;
    protected $password;

    public function __construct()
    {
        $wsdl = config('services.innovation.wsdl');
        $this->user = config('services.innovation.username');
        $this->password = config('services.innovation.password');       
        $this->client = new \nusoap_client($wsdl, 'wsdl');
    }

    public function validateConnection(): array
    {
        $params = [
            'user_api' => $this->user,
            'api_key' => $this->password,
            'format' => 'JSON',
        ];

        $response = $this->client->call('Validate', $params);
        Log::info('Respuesta de validación de Innovation:', ['response' => $response]);

        if (is_string($response)) {
            $decoded = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        if (is_array($response)) {
            return $response;
        }

        return ['response' => false, 'message' => 'Respuesta no válida'];
    }

    public function getPages(): int
    {
        $params = [
            'user_api' => $this->user,
            'api_key' => $this->password,
            'format' => 'JSON',
        ];

        $response = $this->client->call('Pages', $params);
        $decoded = json_decode($response, true);
        return $decoded['pages'] ?? 0;
    }

    public function getProducts(): void
    {
        $pages = $this->getPages();
        $allProducts = [];

        for ($page = 1; $page <= $pages; $page++) {
            $params = array_merge($this->baseParams(), ['page' => $page]);
            $response = $this->client->call('Products', $params);

            if (is_string($response)) {
                $decoded = json_decode($response, true);
                if (isset($decoded['data'])) {
                    $allProducts = array_merge($allProducts, $decoded['data']);
                }
            }
        }

        Storage::put('innovation/products.json', json_encode(['data' => $allProducts], JSON_PRETTY_PRINT));
    }

    public function getStock(): void
    {
        $pages = $this->getPages();
        $allStock = [];

        for ($page = 1; $page <= $pages; $page++) {
            $params = array_merge($this->baseParams(), ['page' => $page]);
            $response = $this->client->call('Stock', $params);

            if (is_string($response)) {
                $decoded = json_decode($response, true);
                if (isset($decoded['data'])) {
                    $allStock = array_merge($allStock, $decoded['data']);
                }
            }
        }

        Storage::put('innovation/stock.json', json_encode(['data' => $allStock], JSON_PRETTY_PRINT));
    }

    public function getSalePrices(): void
    {
        $pages = $this->getPages();
        $allPrices = [];

        for ($page = 1; $page <= $pages; $page++) {
            $params = array_merge($this->baseParams(), ['page' => $page]);
            $response = $this->client->call('SalePrice', $params);

            if (is_string($response)) {
                $decoded = json_decode($response, true);
                if (isset($decoded['data'])) {
                    $allPrices = array_merge($allPrices, $decoded['data']);
                }
            }
        }

        Storage::put('innovation/sale_prices.json', json_encode(['data' => $allPrices], JSON_PRETTY_PRINT));
    }

    protected function baseParams(): array
    {
        return [
            'user_api' => $this->user,
            'api_key' => $this->password,
            'format' => 'JSON',
        ];
    }
}
