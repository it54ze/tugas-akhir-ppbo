@extends('layouts.app')
@section('content')
<section class="page-intro"><span class="eyebrow">THE ONES THAT HIT DIFFERENT</span><h1>Liked <em>Songs</em></h1><p>Your personal hall of fame.</p></section>
@if($songs->count())<div class="song-grid">@foreach($songs as $song) @include('partials.song-card', ['song' => $song, 'playlists' => $playlists ?? collect()]) @endforeach</div>@else<div class="empty-panel"><div class="empty-pixel empty-heart" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><path d="M20.8 8.7c0 5-8.8 10.1-8.8 10.1S3.2 13.7 3.2 8.7A4.7 4.7 0 0 1 12 6.2a4.7 4.7 0 0 1 8.8 2.5Z"/></svg></div><span class="eyebrow">NO FAVORITES JUST YET</span><h3>LOVE A TRACK?<br>KEEP IT CLOSE.</h3><p>Hit the heart on any song to save it here.</p><a class="button button-lime" href="{{ route('library') }}" data-nav>EXPLORE YOUR LIBRARY ↗</a></div>@endif
@endsection
