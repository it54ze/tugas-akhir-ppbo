<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/library', [PageController::class, 'library'])->name('library');
Route::get('/upload', [PageController::class, 'upload'])->name('upload');
Route::get('/search', [PageController::class, 'search'])->name('search');
Route::get('/liked', [PageController::class, 'liked'])->name('liked');
Route::get('/recently-played', [PageController::class, 'recent'])->name('recent');
Route::get('/playlists', [PageController::class, 'playlists'])->name('playlists.index');
Route::get('/playlists/{playlist}', [PageController::class, 'showPlaylist'])->name('playlists.show');
