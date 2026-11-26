<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSongRequest;
use App\Http\Resources\SongResource;
use App\Models\Song;
use App\Services\DeleteSongService;
use App\Services\UploadSongService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SongController extends Controller
{
    public function index(Request $request)
    {
        $query = Song::query()->latest();

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('artist', 'like', "%{$search}%")
                    ->orWhere('album', 'like', "%{$search}%")
                    ->orWhere('genre', 'like', "%{$search}%");
            });
        }

        if ($ids = $request->query('ids')) {
            $idList = collect(explode(',', (string) $ids))
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values();

            $query->whereIn('id', $idList);

            if ($idList->isNotEmpty()) {
                // CASE ordering works on both MySQL and SQLite (used by tests).
                $whenClauses = $idList->map(fn ($id, $index) => 'WHEN ? THEN '.$index)->implode(' ');
                $query->orderByRaw('CASE id '.$whenClauses.' ELSE '.$idList->count().' END', $idList->all());
            }
        }

        $likedIds = \Illuminate\Support\Facades\DB::table('liked_songs')->pluck('song_id')->map(fn ($id) => (int) $id)->all();
        $request->attributes->set('liked_song_ids', $likedIds);

        return SongResource::collection($query->get());
    }

    public function store(StoreSongRequest $request, UploadSongService $service): JsonResponse
    {
        $song = $service->execute($request->validated());
        $request->attributes->set('liked_song_ids', []);

        return (new SongResource($song))->response()->setStatusCode(201);
    }

    public function show(Song $song, Request $request): SongResource
    {
        $likedIds = \Illuminate\Support\Facades\DB::table('liked_songs')->pluck('song_id')->map(fn ($id) => (int) $id)->all();
        $request->attributes->set('liked_song_ids', $likedIds);
        return new SongResource($song);
    }

    public function destroy(Song $song, DeleteSongService $service): JsonResponse
    {
        $service->execute($song);

        return response()->json(['message' => 'Lagu berhasil dihapus.']);
    }
}
