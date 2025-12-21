<?php

namespace App\Services\CFDI;

class StampResult
{
    protected bool $success;
    protected ?string $uuid;
    protected ?string $xml;
    protected ?string $error;
    protected array $metadata;

    private function __construct(
        bool $success,
        ?string $uuid = null,
        ?string $xml = null,
        ?string $error = null,
        array $metadata = []
    ) {
        $this->success = $success;
        $this->uuid = $uuid;
        $this->xml = $xml;
        $this->error = $error;
        $this->metadata = $metadata;
    }

    public static function success(string $uuid, string $xml, array $metadata = []): self
    {
        return new self(true, $uuid, $xml, null, $metadata);
    }

    public static function error(string $message, array $metadata = []): self
    {
        return new self(false, null, null, $message, $metadata);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function getXml(): ?string
    {
        return $this->xml;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'uuid' => $this->uuid,
            'xml' => $this->xml,
            'error' => $this->error,
            'metadata' => $this->metadata,
        ];
    }
}
