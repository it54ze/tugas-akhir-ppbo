@extends('layouts.app')
@section('content')
<section class="page-intro"><span class="eyebrow">THE COLLECTION</span><div class="intro-line"><h1>Your <em>Library</em></h1><a class="button button-lime" href="{{ route('upload') }}" data-nav>＋ UPLOAD MUSIC</a></div><p>Every track here has a story. Yours.</p></section>
<div class="library-toolbar"><span>{{ $songs->count() }} TRACK{{ $songs->count() === 1 ? '' : 'S' }} IN YOUR COLLECTION</span><div class="view-switch"><button class="view-toggle active" data-view="grid">▦ GRID</button><button class="view-toggle" data-view="list">☷ LIST</button></div></div>
@if($songs->count())
    <div class="song-grid" id="library-grid">@foreach($songs as $song) @include('partials.song-card', ['song' => $song, 'playlists' => $playlists]) @endforeach</div>
@else
    <div class="empty-panel library-empty"><div class="empty-pixel">▤</div><span class="eyebrow">A LITTLE TOO QUIET IN HERE</span><h3>YOUR LIBRARY IS<br>WAITING FOR SOME MUSIC.</h3><p>Upload your favorite tracks and build your own collection.</p><a class="button button-pink" href="{{ route('upload') }}" data-nav>＋ UPLOAD MUSIC</a></div>
@endif
@endsection
