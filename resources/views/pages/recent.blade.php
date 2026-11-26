@extends('layouts.app')
@section('content')
<section class="page-intro"><span class="eyebrow">YOUR SOUNDTRACK SO FAR / 05</span><h1>Recently <em>Played</em></h1><p>Tracks you've actually pressed play on.</p></section>
@if($recentSongs->count())<div class="song-grid">@foreach($recentSongs as $song) @include('partials.song-card', ['song' => $song, 'playlists' => $playlists ?? collect()]) @endforeach</div>@else<div class="empty-panel"><div class="empty-pixel">◷</div><span class="eyebrow">THE TAPE IS STILL BLANK</span><h3>YOUR NEXT REPLAY<br>STARTS WITH PLAY.</h3><p>Start listening and your recent tracks will show up here.</p><a class="button button-lime" href="{{ route('library') }}" data-nav>GO TO YOUR LIBRARY ↗</a></div>@endif
@endsection
