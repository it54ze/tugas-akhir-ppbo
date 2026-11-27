<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SongResource;
use App\Models\RecentlyPlayed;
use App\Models\Song;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlaybackController extends Controller
{
    public function index(Request $request)
    {
        $ids = RecentlyPlayed::query()
            ->selectRaw('song_id, MAX(played_at) as last_played_at')
            ->groupBy('song_id')
            ->orderByDesc('last_played_at')
            ->limit(20)
            ->pluck('song_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $likedIds = DB::table('liked_songs')->pluck('song_id')->map(fn ($id) => (int) $id)->all();
        $request->attributes->set('liked_song_ids', $likedIds);

        $songs = Song::whereIn('id', $ids)->get()
            ->sortBy(fn ($song) => array_search($song->id, $ids, true))
            ->values();

        return SongResource::collection($songs);
    }

    public function store(Song $song): JsonResponse
    {
        RecentlyPlayed::create([
            'song_id' => $song->id,
            'played_at' => now(),
        ]);

        return response()->json(['message' => 'Riwayat pemutaran dicatat.']);
    }
}