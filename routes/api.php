<?php

use App\Http\Controllers\Api\LikedSongController;
use App\Http\Controllers\Api\PlaybackController;
use App\Http\Controllers\Api\PlaylistController;
use App\Http\Controllers\Api\SongController;
use Illuminate\Support\Facades\Route;

Route::get('/songs', [SongController::class, 'index']);
Route::post('/songs', [SongController::class, 'store']);
Route::get('/songs/{song}', [SongController::class, 'show']);
Route::delete('/songs/{song}', [SongController::class, 'destroy']);

Route::get('/playlists', [PlaylistController::class, 'index']);
Route::post('/playlists', [PlaylistController::class, 'store']);
Route::get('/playlists/{playlist}', [PlaylistController::class, 'show']);
Route::match(['put', 'patch'], '/playlists/{playlist}', [PlaylistController::class, 'update']);
Route::delete('/playlists/{playlist}', [PlaylistController::class, 'destroy']);
Route::post('/playlists/{playlist}/songs', [PlaylistController::class, 'addSong']);
Route::delete('/playlists/{playlist}/songs', [PlaylistController::class, 'removeSongs']);
Route::delete('/playlists/{playlist}/songs/{song}', [PlaylistController::class, 'removeSong']);

Route::get('/liked-songs', [LikedSongController::class, 'index']);
Route::put('/songs/{song}/like', [LikedSongController::class, 'store']);
Route::delete('/songs/{song}/like', [LikedSongController::class, 'destroy']);

Route::get('/recently-played', [PlaybackController::class, 'index']);
Route::post('/songs/{song}/play', [PlaybackController::class, 'store']);
