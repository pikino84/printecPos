<?php

namespace App\Services\DobleVela;

use Illuminate\Support\Facades\Storage;

class DobleVelaService
{
    protected DobleVelaClient $client;

    public function __construct(DobleVelaClient $client)
    {
        $this->client = $client;
    }

    public function consultarYGuardar()
    {
        $response = $this->client->getExistenciaAll();

        // Convertir a JSON si es un objeto o XML
        $json = json_encode($response, JSON_PRETTY_PRINT);

        // Guardar en storage/app/doblevela/products.json
        Storage::disk('local')->put('doblevela/products.json', $json);

        return $json;
    }

    public function obtenerDesdeArchivo()
    {
        return json_decode(Storage::disk('local')->get('doblevela/products.json'), true);
    }
}
