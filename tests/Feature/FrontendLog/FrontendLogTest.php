<?php

namespace Tests\Feature\FrontendLog;

use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class FrontendLogTest extends TestCase
{
    public function test_acepta_reporte_de_error_js_sin_autenticacion(): void
    {
        Log::shouldReceive('channel')->with('frontend')->once()->andReturnSelf();
        Log::shouldReceive('warning')->once();

        $response = $this->postJson('/frontend-log', [
            'type' => 'js-error',
            'message' => 'Uncaught TypeError: $ is not defined',
            'source' => 'https://posprintec.com/js/script.min.js',
            'line' => 10,
            'column' => 5,
            'url' => '/quotes',
        ]);

        $response->assertNoContent();
    }

    public function test_acepta_reporte_de_loader_atorado_con_diagnostico(): void
    {
        Log::shouldReceive('channel')->with('frontend')->once()->andReturnSelf();
        Log::shouldReceive('warning')->once();

        $response = $this->postJson('/frontend-log', [
            'type' => 'loader-stuck',
            'message' => '10s sin terminar de cargar la página',
            'url' => '/quotes',
            'diagnostics' => [
                'readyState' => 'loading',
                'jquery' => false,
                'scriptsPendientes' => ['https://cdn.jsdelivr.net/npm/sweetalert/dist/sweetalert.min.js'],
            ],
        ]);

        $response->assertNoContent();
    }

    public function test_rechaza_tipo_de_evento_desconocido(): void
    {
        $response = $this->postJson('/frontend-log', [
            'type' => 'cualquier-cosa',
            'message' => 'x',
        ]);

        $response->assertUnprocessable();
    }
}
