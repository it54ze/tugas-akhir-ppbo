<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Models\RecentlyPlayed;
use App\Models\Song;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('pages.home', [
            'songCount' => Song::count(),
            'playlistCount' => Playlist::count(),
            'latestSongs' => Song::latest()->limit(6)->get(),
            'recentSongs' => RecentlyPlayed::with('song')->latest('played_at')->get()->unique('song_id')->take(5)->pluck('song')->filter()->values(),
            'playlists' => Playlist::withCount('songs')->get(),
        ]);
    }

    public function library(): View
    {
        return view('pages.library', ['songs' => Song::latest()->get(), 'playlists' => Playlist::withCount('songs')->get()]);
    }

    public function upload(): View
    {
        return view('pages.upload');
    }

    public function search(): View
    {
        return view('pages.search');
    }

    public function liked(): View
    {
        $songs = Song::whereIn('id', function ($query) {
            $query->select('song_id')->from('liked_songs');
        })->latest()->get();

        return view('pages.liked', ['songs' => $songs, 'playlists' => Playlist::withCount('songs')->get()]);
    }

    public function recent(): View
    {
        $recentSongs = RecentlyPlayed::with('song')->latest('played_at')->get()->unique('song_id')->pluck('song')->filter()->values();
        return view('pages.recent', ['recentSongs' => $recentSongs, 'playlists' => Playlist::withCount('songs')->get()]);
    }

    public function playlists(): View
    {
        return view('pages.playlists', ['playlists' => Playlist::withCount('songs')->latest()->get()]);
    }

    public function showPlaylist(Playlist $playlist): View
    {
        $playlist->load('songs');

        $availableSongs = Song::query()
            ->whereNotIn('id', $playlist->songs->modelKeys())
            ->orderBy('title')
            ->get(['id', 'title', 'artist']);

        return view('pages.playlist-show', [
            'playlist' => $playlist,
            'availableSongs' => $availableSongs,
        ]);
    }
}
