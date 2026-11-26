@extends('layouts.app')
@section('content')
<section class="page-intro">
    <span class="eyebrow">MIX IT YOUR WAY</span>
    <div class="intro-line">
        <h1>Your <em>Playlists</em></h1>
        <button class="button button-pink {{ $playlists->isEmpty() ? 'hidden' : '' }}" type="button" id="new-playlist-button">＋ CREATE PLAYLIST</button>
    </div>
    <p>Make a little world for every mood.</p>
</section>

<form id="create-playlist-form" class="create-playlist-form hidden" enctype="multipart/form-data">
    <div class="playlist-form-fields">
        <span class="eyebrow">NEW MIX TAPE</span>
        <input name="name" maxlength="100" placeholder="Playlist name" required>
        <input name="description" maxlength="500" placeholder="A little description (optional)">
        <label class="playlist-cover-input">＋ ADD COVER ART
            <input type="file" name="cover" accept="image/jpeg,image/png,image/webp">
        </label>
    </div>
    <button class="button button-lime" type="submit">SAVE PLAYLIST ↗</button>
</form>

<section id="new-playlist-song-picker" class="new-playlist-song-picker hidden" aria-live="polite" aria-label="Add songs to the new playlist">
    <div class="new-playlist-picker-heading">
        <div>
            <span class="eyebrow">YOUR MIX IS READY</span>
            <h2 id="new-playlist-picker-title">ADD TRACKS TO YOUR PLAYLIST</h2>
            <p id="new-playlist-picker-status">Choose songs below. You can add more than one.</p>
        </div>
        <a id="new-playlist-open-link" class="button button-outline" href="#">OPEN PLAYLIST ↗</a>
    </div>
    <div id="new-playlist-song-list" class="new-playlist-song-list">
        <p class="playlist-add-empty">Loading your library...</p>
    </div>
</section>

<div class="playlist-grid {{ $playlists->isEmpty() ? 'hidden' : '' }}" id="playlist-grid">
    @foreach($playlists as $playlist)
        <a class="playlist-card" href="{{ route('playlists.show', $playlist) }}" data-nav>
            <div class="playlist-art art-{{ ($loop->index % 4) + 1 }}">
                @if($playlist->cover_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($playlist->cover_path) }}" alt="">
                @else
                    <span>♫</span><small>VYBE<br>MIXTAPE</small>
                @endif
            </div>
            <div class="playlist-card-info">
                <strong>{{ $playlist->name }}</strong>
                <span><span data-playlist-count="{{ $playlist->id }}">{{ $playlist->songs_count }}</span> TRACKS · {{ $playlist->description ?: 'CURATED BY YOU' }}</span>
            </div>
            <span class="playlist-arrow">↗</span>
        </a>
    @endforeach
</div>

<div class="empty-panel {{ $playlists->isNotEmpty() ? 'hidden' : '' }}" id="playlists-empty-state">
    <div class="empty-pixel" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5h16v14H4z"/><path d="M8 5v14M12 9h5M12 13h5"/></svg></div>
    <span class="eyebrow">NO MIXTAPES FOUND</span>
    <h3>MAKE A PLAYLIST.<br>SET A MOOD.</h3>
    <p>Your first playlist is one click away.</p>
    <button class="button button-pink" type="button" id="empty-new-playlist">＋ CREATE YOUR FIRST PLAYLIST</button>
</div>
@endsection
