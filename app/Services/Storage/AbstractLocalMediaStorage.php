<?php

namespace App\Services\Storage;

use App\Contracts\FileStorageInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Abstraction for local media storage. Concrete storage classes inherit
 * the common behavior and define only their own target directory.
 */
abstract class AbstractLocalMediaStorage implements FileStorageInterface
{
    public function __construct(private readonly string $directory)
    {
    }

    public function store(UploadedFile $file): string
    {
        $extension = match ($file->getMimeType()) {
            'audio/mpeg', 'audio/mp3' => 'mp3',
            'audio/wav', 'audio/x-wav', 'audio/wave' => 'wav',
            'audio/ogg', 'application/ogg' => 'ogg',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => $file->guessExtension() ?: 'bin',
        };
        $filename = Str::uuid()->toString().'.'.strtolower($extension);
        $path = $file->storeAs($this->directory, $filename, 'public');

        if ($path === false) {
            throw new \RuntimeException('File gagal disimpan ke storage.');
        }

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path !== null && $path !== '') {
            Storage::disk('public')->delete($path);
        }
    }

    public function url(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }
}
