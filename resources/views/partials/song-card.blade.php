@php
    $isLiked = \Illuminate\Support\Facades\DB::table('liked_songs')->where('song_id', $song->id)->exists();
    $audioUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($song->audio_path);
    $coverUrl = $song->cover_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($song->cover_path) : '';
@endphp
<article class="song-card" data-song-card="{{ $song->id }}" data-song-title="{{ $song->title }}" data-song-artist="{{ $song->artist }}" data-audio-url="{{ $audioUrl }}" data-cover-url="{{ $coverUrl }}" data-is-liked="{{ $isLiked ? 'true' : 'false' }}" tabindex="0" aria-label="Play {{ $song->title }}">
    <div class="song-art-wrap">
        @if($song->cover_path)
            <img class="song-art" src="{{ $coverUrl }}" alt="Cover {{ $song->title }}" loading="lazy">
        @else
            <div class="song-art art-placeholder"><span>♫</span><small>VYBE<br>TRACK</small></div>
        @endif
        <button class="art-play" type="button" data-play="{{ $song->id }}" aria-label="Play {{ $song->title }}">▶</button>
    </div>
    <div class="song-card-info">
        <div class="song-title-line"><strong title="{{ $song->title }}">{{ $song->title }}</strong>
            <button type="button" class="like-button {{ $isLiked ? 'liked' : '' }}" data-like="{{ $song->id }}" aria-label="Like {{ $song->title }}" aria-pressed="{{ $isLiked ? 'true' : 'false' }}">♡</button></div>
        <span>{{ $song->artist }}</span>
    </div>
    <div class="song-card-actions">
        @if(isset($playlists) && $playlists->count())
            <select aria-label="Add {{ $song->title }} to playlist" data-add-to-playlist="{{ $song->id }}"><option value="">＋ Playlist</option>@foreach($playlists as $playlist)<option value="{{ $playlist->id }}">{{ $playlist->name }}</option>@endforeach</select>
        @endif
        <button class="text-action delete-song" type="button" data-delete-song="{{ $song->id }}">REMOVE</button>
    </div>
</article>
