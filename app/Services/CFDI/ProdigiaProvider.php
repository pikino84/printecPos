<?php

namespace App\Services\CFDI;

use App\Models\PartnerEntity;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Proveedor PAC Prodigia (PADE)
 *
 * Implementa la integración con el servicio de timbrado de Prodigia
 * usando su API REST.
 *
 * @see https://docs.prodigia.com.mx/api-timbrado-xml.html
 */
class ProdigiaProvider implements PACInterface
{
    protected string $baseUrl;
    protected string $contrato;
    protected string $usuario;
    protected string $password;
    protected bool $testMode;
    protected array $defaultOptions;
    protected int $timeout;

    public function __construct()
    {
        $config = config('cfdi.prodigia');
        $this->testMode = config('cfdi.test_mode', true);

        $this->baseUrl = $this->testMode
            ? $config['test_url']
            : $config['production_url'];

        $this->contrato = $config['contrato'] ?? '';
        $this->usuario = $config['usuario'] ?? '';
        $this->password = $config['password'] ?? '';
        $this->defaultOptions = $config['stamp_options'] ?? [];
        $this->timeout = $config['timeout'] ?? 60;
    }

    /**
     * Timbrar un XML de CFDI
     */
    public function stamp(string $xml, PartnerEntity $entity): StampResult
    {
        try {
            // Validar credenciales
            if (empty($this->contrato) || empty($this->usuario) || empty($this->password)) {
                return StampResult::error('Credenciales de Prodigia no configuradas');
            }

            // Preparar el XML en base64
            $xmlBase64 = base64_encode($xml);

            // Preparar credenciales CSD si están disponibles y no usamos CALCULAR_SELLO
            $certBase64 = null;
            $keyBase64 = null;
            $keyPass = null;

            if ($entity->hasCsdConfigured() && !in_array('CALCULAR_SELLO', $this->defaultOptions)) {
                $certBase64 = $this->getFileBase64($entity->csd_cer_path);
                $keyBase64 = $this->getFileBase64($entity->csd_key_path);
                $keyPass = $entity->getCsdPasswordDecrypted();
            }

            // Construir el payload
            $payload = [
                'xmlBase64' => $xmlBase64,
                'contrato' => $this->contrato,
                'prueba' => $this->testMode,
                'opciones' => $this->defaultOptions,
            ];

            // Agregar CSD si está disponible
            if ($certBase64 && $keyBase64 && $keyPass) {
                $payload['certBase64'] = $certBase64;
                $payload['keyBase64'] = $keyBase64;
                $payload['keyPass'] = $keyPass;
            }

            // Realizar la petición
            $response = Http::timeout($this->timeout)
                ->withBasicAuth($this->usuario, $this->password)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post("{$this->baseUrl}/timbrado40/timbrarCfdi", $payload);

            // Procesar respuesta
            if (!$response->successful()) {
                Log::error('Prodigia HTTP Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return StampResult::error('Error de conexión con Prodigia: HTTP ' . $response->status());
            }

            $data = $response->json();

            // Verificar si el timbrado fue exitoso
            if (!($data['timbradoOk'] ?? false)) {
                $errorMsg = $data['mensaje'] ?? $data['error'] ?? 'Error desconocido en timbrado';
                $errorCode = $data['codigo'] ?? null;

                Log::warning('Prodigia Stamp Failed', [
                    'code' => $errorCode,
                    'message' => $errorMsg,
                    'response' => $data,
                ]);

                return StampResult::error("Error de timbrado [{$errorCode}]: {$errorMsg}", [
                    'code' => $errorCode,
                    'provider' => 'Prodigia',
                ]);
            }

            // Extraer datos del timbrado exitoso
            $uuid = $data['UUID'] ?? $data['uuid'] ?? null;
            $stampedXml = isset($data['xmlBase64']) ? base64_decode($data['xmlBase64']) : null;
            $pdfBase64 = $data['pdfBase64'] ?? null;

            if (empty($uuid) || empty($stampedXml)) {
                return StampResult::error('Respuesta de Prodigia incompleta: falta UUID o XML');
            }

            Log::info('Prodigia Stamp Success', [
                'uuid' => $uuid,
                'fechaTimbrado' => $data['FechaTimbrado'] ?? null,
            ]);

            return StampResult::success($uuid, $stampedXml, [
                'provider' => 'Prodigia',
                'environment' => $this->testMode ? 'test' : 'production',
                'fechaTimbrado' => $data['FechaTimbrado'] ?? null,
                'selloCFD' => $data['selloCFD'] ?? null,
                'selloSAT' => $data['selloSAT'] ?? null,
                'noCertificadoSAT' => $data['noCertificadoSAT'] ?? null,
                'pdfBase64' => $pdfBase64,
                'saldo' => $data['saldo'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('Prodigia Stamp Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return StampResult::error('Error al conectar con Prodigia: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar un CFDI timbrado
     */
    public function cancel(
        string $uuid,
        PartnerEntity $entity,
        string $reason,
        ?string $replacementUuid = null
    ): CancelResult {
        try {
            // Validar credenciales
            if (empty($this->contrato) || empty($this->usuario) || empty($this->password)) {
                return CancelResult::error('Credenciales de Prodigia no configuradas');
            }

            // Validar que tenga CSD para cancelar
            if (!$entity->hasCsdConfigured()) {
                return CancelResult::error('Se requieren certificados CSD para cancelar');
            }

            // Formatear UUID con motivo: UUID|Motivo|FolioSustituto
            $folioSustitucion = $replacementUuid ?? '';
            $uuidFormateado = "{$uuid}|{$reason}|{$folioSustitucion}";

            // Preparar payload
            $payload = [
                'contrato' => $this->contrato,
                'rfcEmisor' => $entity->rfc,
                'uuid' => [$uuidFormateado],
                'certBase64' => $this->getFileBase64($entity->csd_cer_path),
                'keyBase64' => $this->getFileBase64($entity->csd_key_path),
                'keyPass' => $entity->getCsdPasswordDecrypted(),
                'prueba' => $this->testMode,
            ];

            // Realizar la petición
            $response = Http::timeout($this->timeout)
                ->withBasicAuth($this->usuario, $this->password)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post("{$this->baseUrl}/cancelacion/cancelar", $payload);

            if (!$response->successful()) {
                Log::error('Prodigia Cancel HTTP Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return CancelResult::error('Error de conexión con Prodigia: HTTP ' . $response->status());
            }

            $data = $response->json();

            // Verificar resultado de cancelación
            $cancelacionOk = $data['cancelacionOk'] ?? false;
            $folios = $data['folios'] ?? [];

            if (!$cancelacionOk && empty($folios)) {
                $errorMsg = $data['mensaje'] ?? $data['error'] ?? 'Error desconocido en cancelación';
                return CancelResult::error("Error de cancelación: {$errorMsg}");
            }

            // Procesar el resultado del folio
            $folioResult = $folios[0] ?? null;
            if ($folioResult) {
                $estatusUUID = $folioResult['estatusUUID'] ?? null;
                $estatusCancelacion = $folioResult['estatusCancelacion'] ?? null;

                // Códigos de estatus del SAT
                // 201 = Cancelado exitosamente
                // 202 = En proceso (requiere aceptación del receptor)
                // Otros = Error
                if ($estatusUUID === '201' || $estatusUUID === 201) {
                    Log::info('Prodigia Cancel Success', [
                        'uuid' => $uuid,
                        'estatus' => $estatusUUID,
                    ]);

                    return CancelResult::success('cancelled', [
                        'provider' => 'Prodigia',
                        'environment' => $this->testMode ? 'test' : 'production',
                        'estatusUUID' => $estatusUUID,
                        'estatusCancelacion' => $estatusCancelacion,
                        'acuse' => $data['acuseXmlBase64'] ?? null,
                    ]);
                }

                if ($estatusUUID === '202' || $estatusUUID === 202) {
                    Log::info('Prodigia Cancel Pending', [
                        'uuid' => $uuid,
                        'estatus' => $estatusUUID,
                    ]);

                    return CancelResult::pending([
                        'provider' => 'Prodigia',
                        'environment' => $this->testMode ? 'test' : 'production',
                        'estatusUUID' => $estatusUUID,
                        'message' => 'Cancelación en proceso. Requiere aceptación del receptor.',
                    ]);
                }

                // Otro estatus = error
                $errorMsg = $this->getCancelStatusMessage($estatusUUID);
                return CancelResult::error("Error de cancelación [{$estatusUUID}]: {$errorMsg}");
            }

            return CancelResult::error('Respuesta de cancelación incompleta');

        } catch (\Exception $e) {
            Log::error('Prodigia Cancel Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return CancelResult::error('Error al conectar con Prodigia: ' . $e->getMessage());
        }
    }

    /**
     * Verificar el estado de un CFDI
     */
    public function getStatus(string $uuid, PartnerEntity $entity): array
    {
        try {
            $payload = [
                'contrato' => $this->contrato,
                'rfcEmisor' => $entity->rfc,
                'rfcReceptor' => '', // Se puede agregar si se tiene
                'uuid' => $uuid,
                'total' => '', // Se puede agregar si se tiene
            ];

            $response = Http::timeout($this->timeout)
                ->withBasicAuth($this->usuario, $this->password)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post("{$this->baseUrl}/cancelacion/consultarEstatusComprobante", $payload);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'error' => 'Error de conexión: HTTP ' . $response->status(),
                ];
            }

            $data = $response->json();

            return [
                'success' => true,
                'uuid' => $uuid,
                'status' => $data['estado'] ?? 'Desconocido',
                'cancellable' => ($data['esCancelable'] ?? 'No') === 'Cancelable',
                'cancellation_status' => $data['estatusCancelacion'] ?? null,
                'provider' => 'Prodigia',
                'raw_response' => $data,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Obtener el nombre del proveedor
     */
    public function getProviderName(): string
    {
        $env = $this->testMode ? 'Pruebas' : 'Producción';
        return "Prodigia ({$env})";
    }

    /**
     * Consultar saldo de timbres disponibles
     */
    public function getBalance(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withBasicAuth($this->usuario, $this->password)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post("{$this->baseUrl}/timbrado40/consultarSaldo", [
                    'contrato' => $this->contrato,
                ]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'error' => 'Error de conexión: HTTP ' . $response->status(),
                ];
            }

            $data = $response->json();

            return [
                'success' => true,
                'saldo' => $data['saldo'] ?? 0,
                'provider' => 'Prodigia',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Recuperar CFDI por UUID
     */
    public function getCfdiByUuid(string $uuid): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withBasicAuth($this->usuario, $this->password)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post("{$this->baseUrl}/timbrado40/cfdiPorUUID", [
                    'contrato' => $this->contrato,
                    'uuid' => $uuid,
                ]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'error' => 'Error de conexión: HTTP ' . $response->status(),
                ];
            }

            $data = $response->json();

            if (empty($data['xmlBase64'])) {
                return [
                    'success' => false,
                    'error' => $data['mensaje'] ?? 'CFDI no encontrado',
                ];
            }

            return [
                'success' => true,
                'xml' => base64_decode($data['xmlBase64']),
                'provider' => 'Prodigia',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Enviar CFDI por correo electrónico
     */
    public function sendByEmail(string $uuid, array $emails): array
    {
        try {
            if (count($emails) > 20) {
                return [
                    'success' => false,
                    'error' => 'Máximo 20 correos permitidos',
                ];
            }

            $response = Http::timeout($this->timeout)
                ->withBasicAuth($this->usuario, $this->password)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post("{$this->baseUrl}/timbrado40/enviarXmlAndPdfPorCorreo", [
                    'contrato' => $this->contrato,
                    'uuid' => $uuid,
                    'correos' => $emails,
                ]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'error' => 'Error de conexión: HTTP ' . $response->status(),
                ];
            }

            $data = $response->json();

            return [
                'success' => $data['envioOk'] ?? false,
                'message' => $data['mensaje'] ?? null,
                'provider' => 'Prodigia',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Obtener archivo en base64
     */
    protected function getFileBase64(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        try {
            if (Storage::exists($path)) {
                return base64_encode(Storage::get($path));
            }

            // Intentar como ruta absoluta
            if (file_exists($path)) {
                return base64_encode(file_get_contents($path));
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('Error reading CSD file', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Obtener mensaje de estatus de cancelación
     */
    protected function getCancelStatusMessage(string|int $status): string
    {
        $messages = [
            '201' => 'UUID Cancelado exitosamente',
            '202' => 'UUID Pendiente de cancelación',
            '203' => 'UUID no corresponde al emisor',
            '204' => 'UUID no aplicable para cancelación',
            '205' => 'UUID no existe',
            '206' => 'UUID ya cancelado previamente',
            '207' => 'Error en la cancelación',
            '208' => 'UUID no corresponde a un CFDI del ejercicio fiscal actual',
        ];

        return $messages[(string)$status] ?? "Estatus desconocido: {$status}";
    }

    /**
     * Verificar si las credenciales están configuradas
     */
    public function isConfigured(): bool
    {
        return !empty($this->contrato)
            && !empty($this->usuario)
            && !empty($this->password);
    }

    /**
     * Obtener la URL base actual
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Verificar si está en modo de pruebas
     */
    public function isTestMode(): bool
    {
        return $this->testMode;
    }
}
