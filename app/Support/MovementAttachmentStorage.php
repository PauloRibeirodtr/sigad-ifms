<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MovementAttachmentStorage
{
    private const DISK = 'local';

    /**
     * @return array{path: string, original_name: string}
     */
    public function store(UploadedFile $attachment, int $userId): array
    {
        $path = $attachment->store(
            sprintf('movimentacoes/%d', $userId),
            self::DISK,
        );

        if ($path === false) {
            throw new RuntimeException('Não foi possível armazenar o anexo da movimentação.');
        }

        return [
            'path' => $path,
            'original_name' => basename($attachment->getClientOriginalName()),
        ];
    }

    public function delete(?string $path): void
    {
        if ($path !== null) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    public function download(string $path, string $originalName): StreamedResponse
    {
        abort_unless(Storage::disk(self::DISK)->exists($path), 404);

        return Storage::disk(self::DISK)->download($path, $originalName);
    }
}
