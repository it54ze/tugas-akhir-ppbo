<?php

namespace App\Services;

use App\Contracts\CoverStorageInterface;
use App\Models\Playlist;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

/** Handles playlist creation and updates, including optional cover storage. */
class SavePlaylistService
{
    public function __construct(private readonly CoverStorageInterface $coverStorage)
    {
    }

    public function create(array $data): Playlist
    {
        $cover = $data['cover'] ?? null;
        unset($data['cover']);

        $coverPath = $cover instanceof UploadedFile ? $this->coverStorage->store($cover) : null;

        try {
            return DB::transaction(fn () => Playlist::create([
                ...$data,
                'cover_path' => $coverPath,
            ]));
        } catch (Throwable $exception) {
            $this->coverStorage->delete($coverPath);
            throw $exception;
        }
    }

    public function update(Playlist $playlist, array $data): Playlist
    {
        $cover = $data['cover'] ?? null;
        unset($data['cover']);
        $oldCoverPath = $playlist->cover_path;
        $newCoverPath = $cover instanceof UploadedFile ? $this->coverStorage->store($cover) : null;

        try {
            DB::transaction(function () use ($playlist, $data, $newCoverPath, $cover): void {
                $playlist->update([
                    ...$data,
                    ...($cover instanceof UploadedFile ? ['cover_path' => $newCoverPath] : []),
                ]);
            });
        } catch (Throwable $exception) {
            $this->coverStorage->delete($newCoverPath);
            throw $exception;
        }

        if ($newCoverPath !== null && $oldCoverPath !== null) {
            $this->coverStorage->delete($oldCoverPath);
        }

        return $playlist->refresh();
    }
}
