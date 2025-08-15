<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    
    'doblevela' => [
        'key' => env('DOBLEVELA_KEY'),
        'wsdl' => env('DOBLEVELA_WSDL'),
    ],

    'innovation' => [
        'wsdl' => env('INNOVATION_API_BASE_URL'),
        'username' => env('INNOVATION_API_USER'),
        'password' => env('INNOVATION_API_PASSWORD'),
    ],
    'innovation_v3' => [
        'auth_token' => env('INNO_V3_AUTH_TOKEN'),
        'user'       => env('INNO_V3_USER'),
        'password'   => env('INNO_V3_PASS'),
        // Endpoints del manual (puedes moverlos a .env si quieres):
        'endpoints'  => [
            'GetProducto'        => 'https://zbobxn2ot3.execute-api.us-east-1.amazonaws.com/default/Innovation_get_Producto',
            'GetAllProducts'     => 'https://4vumtdis3m.execute-api.us-east-1.amazonaws.com/default/Innovation_GetAllProductos',
            'GetAllProductslight'=> 'https://1x4nyx8c80.execute-api.us-east-1.amazonaws.com/default/Innovation_GetAll_ProducLight',
            'GetAllVariantes'    => 'https://9tlzim70va.execute-api.us-east-1.amazonaws.com/default/Innovation_GetAllVariantes',
        ],
        'page_limit' => 1500, // recomendado por el manual
    ],

];
