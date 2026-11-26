<?php

namespace App\Services;

use App\Contracts\CoverStorageInterface;
use App\Models\Playlist;
use Illuminate\Support\Facades\DB;

/** Deletes a playlist record and its optional artwork, never its songs. */
class DeletePlaylistService
{
    public function __construct(private readonly CoverStorageInterface $coverStorage)
    {
    }

    public function execute(Playlist $playlist): void
    {
        $coverPath = $playlist->cover_path;

        DB::transaction(fn () => $playlist->delete());

        $this->coverStorage->delete($coverPath);
    }
}
