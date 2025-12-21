<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Proveedor PAC por defecto
    |--------------------------------------------------------------------------
    |
    | Opciones: 'mock', 'prodigia'
    | En desarrollo usa 'mock' para simular timbrado sin costo
    |
    */
    'default_provider' => env('CFDI_PROVIDER', 'mock'),

    /*
    |--------------------------------------------------------------------------
    | Modo de pruebas
    |--------------------------------------------------------------------------
    |
    | Si es true, se usará el ambiente de pruebas del PAC
    |
    */
    'test_mode' => env('CFDI_TEST_MODE', true),

    /*
    |--------------------------------------------------------------------------
    | Configuración de Prodigia (PADE)
    |--------------------------------------------------------------------------
    */
    'prodigia' => [
        // URLs de los servicios
        'production_url' => 'https://timbrado.pade.mx/servicio/rest',
        'test_url' => 'https://pruebas.pade.mx/servicio/rest',

        // Credenciales
        'contrato' => env('PRODIGIA_CONTRATO'),
        'usuario' => env('PRODIGIA_USUARIO'),
        'password' => env('PRODIGIA_PASSWORD'),

        // Opciones por defecto para timbrado
        'stamp_options' => [
            'CALCULAR_SELLO',    // El PAC calcula el sello del CFDI
            'GENERAR_PDF',       // Generar PDF junto con el timbrado
            'GENERAR_CBB',       // Generar código QR
        ],

        // Timeout en segundos para las peticiones
        'timeout' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración del XML
    |--------------------------------------------------------------------------
    */
    'xml' => [
        'version' => '4.0',
        'default_currency' => 'MXN',
        'default_export' => '01', // No aplica exportación
    ],

    /*
    |--------------------------------------------------------------------------
    | Motivos de cancelación SAT
    |--------------------------------------------------------------------------
    */
    'cancellation_reasons' => [
        '01' => 'Comprobantes emitidos con errores con relación',
        '02' => 'Comprobantes emitidos con errores sin relación',
        '03' => 'No se llevó a cabo la operación',
        '04' => 'Operación nominativa relacionada en una factura global',
    ],

    /*
    |--------------------------------------------------------------------------
    | Formas de pago SAT
    |--------------------------------------------------------------------------
    */
    'payment_forms' => [
        '01' => 'Efectivo',
        '02' => 'Cheque nominativo',
        '03' => 'Transferencia electrónica de fondos',
        '04' => 'Tarjeta de crédito',
        '05' => 'Monedero electrónico',
        '06' => 'Dinero electrónico',
        '08' => 'Vales de despensa',
        '12' => 'Dación en pago',
        '13' => 'Pago por subrogación',
        '14' => 'Pago por consignación',
        '15' => 'Condonación',
        '17' => 'Compensación',
        '23' => 'Novación',
        '24' => 'Confusión',
        '25' => 'Remisión de deuda',
        '26' => 'Prescripción o caducidad',
        '27' => 'A satisfacción del acreedor',
        '28' => 'Tarjeta de débito',
        '29' => 'Tarjeta de servicios',
        '30' => 'Aplicación de anticipos',
        '31' => 'Intermediario pagos',
        '99' => 'Por definir',
    ],

    /*
    |--------------------------------------------------------------------------
    | Métodos de pago SAT
    |--------------------------------------------------------------------------
    */
    'payment_methods' => [
        'PUE' => 'Pago en una sola exhibición',
        'PPD' => 'Pago en parcialidades o diferido',
    ],

    /*
    |--------------------------------------------------------------------------
    | Usos de CFDI SAT
    |--------------------------------------------------------------------------
    */
    'cfdi_uses' => [
        'G01' => 'Adquisición de mercancías',
        'G02' => 'Devoluciones, descuentos o bonificaciones',
        'G03' => 'Gastos en general',
        'I01' => 'Construcciones',
        'I02' => 'Mobiliario y equipo de oficina por inversiones',
        'I03' => 'Equipo de transporte',
        'I04' => 'Equipo de cómputo y accesorios',
        'I05' => 'Dados, troqueles, moldes, matrices y herramental',
        'I06' => 'Comunicaciones telefónicas',
        'I07' => 'Comunicaciones satelitales',
        'I08' => 'Otra maquinaria y equipo',
        'D01' => 'Honorarios médicos, dentales y gastos hospitalarios',
        'D02' => 'Gastos médicos por incapacidad o discapacidad',
        'D03' => 'Gastos funerales',
        'D04' => 'Donativos',
        'D05' => 'Intereses reales efectivamente pagados por créditos hipotecarios (casa habitación)',
        'D06' => 'Aportaciones voluntarias al SAR',
        'D07' => 'Primas por seguros de gastos médicos',
        'D08' => 'Gastos de transportación escolar obligatoria',
        'D09' => 'Depósitos en cuentas para el ahorro, primas que tengan como base planes de pensiones',
        'D10' => 'Pagos por servicios educativos (colegiaturas)',
        'S01' => 'Sin efectos fiscales',
        'CP01' => 'Pagos',
        'CN01' => 'Nómina',
    ],
];
