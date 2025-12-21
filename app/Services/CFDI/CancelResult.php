<?php

namespace App\Services\CFDI;

class CancelResult
{
    protected bool $success;
    protected ?string $status;
    protected ?string $error;
    protected array $metadata;

    private function __construct(
        bool $success,
        ?string $status = null,
        ?string $error = null,
        array $metadata = []
    ) {
        $this->success = $success;
        $this->status = $status;
        $this->error = $error;
        $this->metadata = $metadata;
    }

    public static function success(string $status = 'cancelled', array $metadata = []): self
    {
        return new self(true, $status, null, $metadata);
    }

    public static function pending(array $metadata = []): self
    {
        return new self(true, 'pending', null, $metadata);
    }

    public static function error(string $message, array $metadata = []): self
    {
        return new self(false, null, $message, $metadata);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function getStatus(): ?string
    {
        return $this->status;
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
            'status' => $this->status,
            'error' => $this->error,
            'metadata' => $this->metadata,
        ];
    }
}
