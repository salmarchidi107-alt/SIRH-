<?php

declare(strict_types=1);

namespace App\Services\OCR;

use App\Services\OCR\Providers\OCRSpaceProvider;
use InvalidArgumentException;


final class OCRManager
{
    /** @var array<string, OCRProviderInterface> */
    private array $resolved = [];

    public function __construct(
        private readonly string $defaultDriver,
    ) {
    }

    public function driver(?string $name = null): OCRProviderInterface
    {
        $name ??= $this->defaultDriver;

        return $this->resolved[$name] ??= $this->make($name);
    }

    private function make(string $name): OCRProviderInterface
    {
        return match ($name) {
            'ocrspace' => new OCRSpaceProvider(
                apiKey: (string) config('services.ocrspace.key'),
                timeoutSeconds: (int) config('services.ocrspace.timeout', 30),
                language: (string) config('services.ocrspace.language', 'fre'),
            ),
            // Emplacement prêt pour un futur fournisseur :
            // 'mindee' => new MindeeProvider(...),
            // 'textract' => new TextractProvider(...),
            default => throw new InvalidArgumentException(
                "Fournisseur OCR inconnu : [{$name}]. Seul 'ocrspace' est disponible. Vérifiez OCR_DRIVER dans le .env."
            ),
        };
    }
}
