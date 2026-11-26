<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlaylistRequest;
use App\Http\Resources\PlaylistResource;
use App\Models\Playlist;
use App\Models\Song;
use App\Services\DeletePlaylistService;
use App\Services\SavePlaylistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlaylistController extends Controller
{
    public function index()
    {
        return PlaylistResource::collection(Playlist::withCount('songs')->latest()->get());
    }

    public function store(StorePlaylistRequest $request, SavePlaylistService $service): JsonResponse
    {
        $playlist = $service->create($request->validated());
        return (new PlaylistResource($playlist->loadCount('songs')))->response()->setStatusCode(201);
    }

    public function show(Playlist $playlist): PlaylistResource
    {
        return new PlaylistResource($playlist->load('songs')->loadCount('songs'));
    }

    public function update(StorePlaylistRequest $request, Playlist $playlist, SavePlaylistService $service): PlaylistResource
    {
        $playlist = $service->update($playlist, $request->validated());
        return new PlaylistResource($playlist->loadCount('songs'));
    }

    public function destroy(Playlist $playlist, DeletePlaylistService $service): JsonResponse
    {
        $service->execute($playlist);
        return response()->json(['message' => 'Playlist berhasil dihapus.']);
    }

    public function addSong(Request $request, Playlist $playlist): JsonResponse
    {
        $validated = $request->validate([
            'song_id' => ['required', 'integer', 'exists:songs,id'],
        ]);

        $songId = (int) $validated['song_id'];
        $alreadyAdded = $playlist->songs()->whereKey($songId)->exists();

        // syncWithoutDetaching keeps the relationship idempotent: repeated
        // submissions never create duplicate playlist_song rows.
        $playlist->songs()->syncWithoutDetaching([$songId]);

        return response()->json([
            'message' => $alreadyAdded
                ? 'Lagu tersebut sudah ada di playlist.'
                : 'Lagu berhasil ditambahkan ke playlist.',
            'data' => [
                'playlist_id' => $playlist->id,
                'song_id' => $songId,
                'songs_count' => $playlist->songs()->count(),
                'already_added' => $alreadyAdded,
            ],
        ]);
    }

    public function removeSong(Playlist $playlist, Song $song): JsonResponse
    {
        $playlist->songs()->detach($song->id);
        return response()->json(['message' => 'Lagu dihapus dari playlist.']);
    }
}
