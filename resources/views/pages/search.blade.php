@extends('layouts.app')
@section('content')
<section class="page-intro"><span class="eyebrow">FIND THAT FEELING</span><h1>What's the <em>vibe?</em></h1><p>Search through the music you have uploaded.</p></section>
<div class="search-box"><span class="search-field-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 5 5"/></svg></span><input id="music-search" type="search" placeholder="Search songs, artists, albums or genres..." autocomplete="off"><kbd>ENTER</kbd></div>
<div class="search-meta"><span id="search-count">YOUR LIBRARY, YOUR SEARCH</span><span>NO EXTERNAL CATALOG</span></div>
<div class="song-grid" id="search-results"></div>
<div id="search-empty" class="empty-panel compact-empty"><div class="empty-pixel" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 5 5"/></svg></div><h3>TYPE A NAME.<br>FIND A FEELING.</h3><p>Search starts from your own uploaded tracks.</p></div>
@endsection
