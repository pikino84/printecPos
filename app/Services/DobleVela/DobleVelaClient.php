<?php

namespace App\Services\DobleVela;

use SoapClient;
use Illuminate\Support\Facades\Log;

class DobleVelaClient
{
    protected ?SoapClient $client = null;

    protected function getClient(): SoapClient
    {
        if ($this->client === null) {
            $wsdl = config('services.doblevela.wsdl');

            $this->client = new SoapClient($wsdl, [
                'trace' => true,
                'exceptions' => true,
                'cache_wsdl' => WSDL_CACHE_BOTH,
                'stream_context' => stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ]),
                'connection_timeout' => 60,
                'default_socket_timeout' => 300,
            ]);
        }

        return $this->client;
    }

    /**
     * GetExistencia - Consulta existencia de UN modelo.
     * Disponible 24 horas.
     *
     * @param string $modelo Código de MODELO a consultar
     * @return array Respuesta parseada con intCodigo, strMensaje, Resultado
     */
    public function getExistencia(string $modelo): array
    {
        $key = config('services.doblevela.key');
        $response = $this->getClient()->GetExistencia([
            'codigo' => $modelo,
            'Key' => $key,
        ]);

        return $this->parseResponse($response, 'GetExistenciaResult');
    }

    /**
     * GetExistenciaAll - Consulta todas las existencias y precios.
     * Disponible SOLO de 8:00 PM a 8:00 AM (CDMX).
     *
     * @return array Respuesta parseada con intCodigo, strMensaje, Resultado
     */
    public function getExistenciaAll(): array
    {
        $key = config('services.doblevela.key');
        $response = $this->getClient()->GetExistenciaAll([
            'Key' => $key,
        ]);

        return $this->parseResponse($response, 'GetExistenciaAllResult');
    }

    /**
     * GetrProdImagenes - Descarga imágenes de 1 a 20 modelos.
     *
     * @param array $modelos Array de códigos de modelo (máx 20)
     * @return array Respuesta con CLAVES y MODELOS (imágenes en base64)
     */
    public function getProductImagenes(array $modelos): array
    {
        $key = config('services.doblevela.key');
        $codigo = json_encode(['CLAVES' => array_slice($modelos, 0, 20)]);

        $response = $this->getClient()->GetrProdImagenes([
            'codigo' => $codigo,
            'Key' => $key,
        ]);

        return $this->parseResponse($response, 'GetrProdImagenesResult');
    }

    /**
     * Parsea la respuesta SOAP (stdClass) a array PHP.
     */
    private function parseResponse($response, string $resultKey): array
    {
        $responseArray = json_decode(json_encode($response), true);

        // Estructura típica SOAP .NET: { ResultKey: "json string" }
        if (isset($responseArray[$resultKey])) {
            $result = $responseArray[$resultKey];

            if (is_string($result)) {
                $decoded = json_decode($result, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
            }

            if (is_array($result)) {
                return $result;
            }
        }

        // Fallback: buscar Resultado directamente
        if (isset($responseArray['Resultado'])) {
            return $responseArray;
        }

        Log::warning('DobleVelaClient: estructura de respuesta inesperada', [
            'result_key' => $resultKey,
            'keys' => array_keys($responseArray),
        ]);

        return $responseArray;
    }
}
