<?php

namespace App\Services;

use App\Models\Playlist;
use Illuminate\Support\Facades\DB;

/** Removes only selected songs attached to a playlist, without deleting library songs. */
class RemovePlaylistSongsService
{
    /**
     * @param  array<int, int>  $songIds
     */
    public function execute(Playlist $playlist, array $songIds): int
    {
        return DB::transaction(function () use ($playlist, $songIds): int {
            $attachedSongIds = $playlist->songs()
                ->whereIn('songs.id', $songIds)
                ->pluck('songs.id')
                ->map(static fn ($id): int => (int) $id)
                ->all();

            if ($attachedSongIds === []) {
                return 0;
            }

            return $playlist->songs()->detach($attachedSongIds);
        });
    }
}
