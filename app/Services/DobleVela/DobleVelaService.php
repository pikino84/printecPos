<?php

namespace App\Services\DobleVela;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DobleVelaService
{
    protected DobleVelaClient $client;

    public function __construct(DobleVelaClient $client)
    {
        $this->client = $client;
    }

    public function syncProducts()    
    {
        $response = $this->client->getExistenciaAll();
        //log
        Log::info('Respuesta de Doble Vela OK');

        // Convertir a JSON si es un objeto o XML
        $json = $response->GetExistenciaAllResult;        
        $data = json_decode($json, true);
        

        if ($data['intCodigo'] == 100) {
                        
            Log::error("Horario aproximado 11am a 7pm Cancun. " . $data['strMensaje']);
            
            return null;
        }else {
            // Guardar el JSON en un archivo
            log::info('Guardando archivo JSON', ['path' => 'doblevela/products.json']);
            $result = $data['Resultado'];
            $json = json_encode($result, JSON_PRETTY_PRINT);
            Storage::disk('local')->put('doblevela/products.json', $json);
        }

        return $json;
    }
}
