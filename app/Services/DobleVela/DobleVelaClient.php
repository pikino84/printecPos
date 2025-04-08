<?php

namespace App\Services\DobleVela;

use SoapClient;

class DobleVelaClient
{
    protected SoapClient $client;

    public function __construct()
    {
        $this->client = new SoapClient(config('services.doblevela.wsdl'));
    }

    public function getExistenciaAll(): mixed
    {
        $params = ['Key' => config('services.doblevela.key')];
        return $this->client->GetExistenciaAll($params);
    }
}
