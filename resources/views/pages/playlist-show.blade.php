@extends('layouts.app')
@section('content')
<section class="playlist-hero">
    <div class="playlist-art large-art art-{{ ($playlist->id % 4) + 1 }}">
        @if($playlist->cover_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($playlist->cover_path) }}" alt="Playlist cover">
        @else
            <span>♫</span>
        @endif
    </div>
    <div class="playlist-hero-copy">
        <span class="eyebrow">YOUR PERSONAL MIXTAPE</span>
        <h1>{{ $playlist->name }}</h1>
        <p>{{ $playlist->description ?: 'A collection made by you, for you.' }}</p>
        <span class="playlist-count">{{ $playlist->songs->count() }} TRACKS</span>
        <div class="playlist-hero-actions">
            <button class="button button-lime" type="button" data-play-list="{{ $playlist->songs->pluck('id')->implode(',') }}">▶ PLAY ALL</button>
            <button class="button button-outline" type="button" data-edit-playlist="{{ $playlist->id }}" data-name="{{ $playlist->name }}" data-description="{{ $playlist->description }}">EDIT DETAILS</button>
            <button class="text-action danger-text" type="button" data-delete-playlist="{{ $playlist->id }}">DELETE PLAYLIST</button>
        </div>
    </div>
</section>

<section class="playlist-add-panel" aria-label="Add a song to this playlist">
    <div class="playlist-add-copy">
        <span class="eyebrow">ADD TO THIS MIX</span>
        <p>Choose another track from your library to add it here.</p>
    </div>
    @if($availableSongs->isNotEmpty())
        <form class="playlist-add-form" data-playlist-add-form data-playlist-id="{{ $playlist->id }}">
            <select id="playlist-add-song" name="song_id" aria-label="Choose a song to add" required>
                <option value="">Choose a track...</option>
                @foreach($availableSongs as $availableSong)
                    <option value="{{ $availableSong->id }}">{{ $availableSong->title }} — {{ $availableSong->artist }}</option>
                @endforeach
            </select>
            <button class="button button-lime" type="submit">＋ ADD TRACK</button>
        </form>
    @else
        <p class="playlist-add-empty">All uploaded tracks are already in this playlist, or your library is empty.</p>
    @endif
</section>

@if($playlist->songs->count())
    <div class="library-toolbar playlist-tracklist-toolbar">
        <span>TRACKLIST / {{ $playlist->songs->count() }} SONGS · CURATED BY YOU</span>
        <button class="button button-outline danger-text" type="button" data-remove-selected-songs="{{ $playlist->id }}" disabled>REMOVE SELECTED (0)</button>
    </div>
    <div class="playlist-tracklist" data-playlist-tracklist="{{ $playlist->id }}">
        @foreach($playlist->songs as $song)
            <div class="track-row" data-song-card="{{ $song->id }}" data-playlist-track-id="{{ $song->id }}" data-song-title="{{ $song->title }}" data-song-artist="{{ $song->artist }}" data-audio-url="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($song->audio_path) }}" data-cover-url="{{ $song->cover_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($song->cover_path) : '' }}" data-is-liked="{{ \Illuminate\Support\Facades\DB::table('liked_songs')->where('song_id', $song->id)->exists() ? 'true' : 'false' }}" tabindex="0" aria-label="Play {{ $song->title }}">
                <label class="playlist-track-select" aria-label="Select {{ $song->title }} for removal">
                    <input type="checkbox" data-playlist-track-select value="{{ $song->id }}">
                    <span></span>
                </label>
                <button class="track-play" type="button" data-play="{{ $song->id }}" aria-label="Play {{ $song->title }}">▶</button>
                <div class="track-row-art">
                    @if($song->cover_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($song->cover_path) }}" alt="">
                    @else
                        ♫
                    @endif
                </div>
                <div class="track-row-copy"><strong>{{ $song->title }}</strong><span>{{ $song->artist }}</span></div>
                <span class="track-genre">{{ $song->genre ?: '—' }}</span>
                <button class="text-action danger-text" type="button" data-remove-playlist-song="{{ $song->id }}" data-playlist-id="{{ $playlist->id }}">REMOVE</button>
            </div>
        @endforeach
    </div>
@else
    <div class="empty-panel compact-empty playlist-empty-state">
        <div class="empty-pixel">♫</div>
        <h3>THIS MIXTAPE IS<br>WAITING FOR A TRACK.</h3>
        <p>Use the Add to This Mix control above to add a track.</p>
        <a class="button button-lime" href="{{ route('library') }}" data-nav>GO TO LIBRARY ↗</a>
    </div>
@endif
@endsection
