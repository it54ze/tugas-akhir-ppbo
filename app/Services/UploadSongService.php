<?php

namespace App\Services;

use App\Contracts\AudioStorageInterface;
use App\Contracts\CoverStorageInterface;
use App\Contracts\FileStorageInterface;
use App\Data\SongMetadata;
use Illuminate\Http\UploadedFile;
use App\Models\Song;
use Illuminate\Support\Facades\DB;
use Throwable;

/** Owns the upload use-case; the controller only handles HTTP concerns. */
class UploadSongService
{
    public function __construct(
        private readonly AudioStorageInterface $audioStorage,
        private readonly CoverStorageInterface $coverStorage,
    ) {
    }

    public function execute(array $data): Song
    {
        $metadata = SongMetadata::fromArray($data);
        $audioPath = $this->storeFile($this->audioStorage, $data['audio']);
        $coverPath = null;

        try {
            if (! empty($data['cover'])) {
                $coverPath = $this->storeFile($this->coverStorage, $data['cover']);
            }

            return DB::transaction(fn () => Song::create([
                ...$metadata->toArray(),
                'audio_path' => $audioPath,
                'cover_path' => $coverPath,
            ]));
        } catch (Throwable $exception) {
            $this->audioStorage->delete($audioPath);
            $this->coverStorage->delete($coverPath);
            throw $exception;
        }
    }

    /**
     * One contract accepts both concrete storage classes. This is a direct
     * polymorphism example: LocalAudioStorage and LocalCoverStorage can both
     * be used through the same FileStorageInterface type.
     */
    private function storeFile(FileStorageInterface $storage, UploadedFile $file): string
    {
        return $storage->store($file);
    }
}
