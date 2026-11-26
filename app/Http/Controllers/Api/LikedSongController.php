<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SongResource;
use App\Models\Song;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LikedSongController extends Controller
{
    public function index(Request $request)
    {
        $ids = DB::table('liked_songs')->orderByDesc('created_at')->pluck('song_id')->map(fn ($id) => (int) $id)->all();
        $request->attributes->set('liked_song_ids', $ids);
        $songs = Song::whereIn('id', $ids)->get()->sortBy(fn ($song) => array_search($song->id, $ids, true))->values();
        return SongResource::collection($songs);
    }

    public function store(Song $song): JsonResponse
    {
        DB::table('liked_songs')->updateOrInsert(['song_id' => $song->id], ['created_at' => now()]);
        return response()->json(['message' => 'Lagu ditambahkan ke Liked Songs.', 'liked' => true]);
    }

    public function destroy(Song $song): JsonResponse
    {
        DB::table('liked_songs')->where('song_id', $song->id)->delete();
        return response()->json(['message' => 'Lagu dihapus dari Liked Songs.', 'liked' => false]);
    }
}
