@extends('layouts.app')
@section('content')
<section class="hero-panel pixel-frame arcade-hero arcade-welcome-screen">
    <div class="arcade-hud">
        <span class="arcade-hud-brand"><i aria-hidden="true"></i> VYBETIFY ARCADE</span>
        <span class="arcade-hud-mode">FREE PLAY <b>•</b> 1 PLAYER</span>
    </div>

    <div class="tetris-decor" aria-hidden="true">
        <div class="tetris-piece tetris-piece-t"><i></i><i></i><i></i><i></i></div>
        <div class="tetris-piece tetris-piece-l"><i></i><i></i><i></i><i></i></div>
        <div class="tetris-piece tetris-piece-z"><i></i><i></i><i></i><i></i></div>
        <div class="tetris-piece tetris-piece-o"><i></i><i></i><i></i><i></i></div>
        <span class="arcade-pixel-dot dot-blue"></span>
        <span class="arcade-pixel-dot dot-pink"></span>
        <span class="arcade-pixel-dot dot-cyan"></span>
        <span class="arcade-pixel-dot dot-lime"></span>
        <span class="arcade-pixel-dot dot-yellow"></span>
        <span class="arcade-pixel-plus plus-lime"></span>
        <span class="arcade-pixel-plus plus-pink"></span>
    </div>

    <div class="arcade-sprite-layer" aria-hidden="true">
        <img class="arcade-sprite arcade-sprite-pacman" src="{{ asset('images/arcade/pacman.webp') }}" alt="">
        <img class="arcade-sprite arcade-sprite-ghost-pink" src="{{ asset('images/arcade/ghost-pink.webp') }}" alt="">
        <img class="arcade-sprite arcade-sprite-cherries" src="{{ asset('images/arcade/pixel-cherries.webp') }}" alt="">
        <img class="arcade-sprite arcade-sprite-star" src="{{ asset('images/arcade/pixel-star.webp') }}" alt="">
        <img class="arcade-sprite arcade-sprite-clover" src="{{ asset('images/arcade/pixel-clover.webp') }}" alt="">
        <img class="arcade-sprite arcade-sprite-ghost-cyan" src="{{ asset('images/arcade/ghost-cyan.webp') }}" alt="">
    </div>

    <div class="hero-topline arcade-quote-line">
        <span class="hero-quote"><i aria-hidden="true">✦</i><span>SOME SONGS SAY WHAT WORDS CAN'T.</span></span>
    </div>

    <div class="arcade-welcome-copy">
        <p class="arcade-prompt">PLAYER ONE, READY?</p>
        <h1 class="arcade-welcome-title">WELCOME</h1>
        <p class="arcade-welcome-tagline">YOUR MUSIC. <em>YOUR RULES.</em></p>
        <p class="hero-subtitle">Your music. Your mood. Your vibe.</p>
        <a class="button button-lime arcade-start-button" href="{{ route('upload') }}" data-nav>
            <span class="start-button-icon" aria-hidden="true">▶</span>
            UPLOAD YOUR FIRST TRACK
        </a>
    </div>

    <div class="hero-stats arcade-stats">
        <div><strong>{{ $songCount }}</strong><span>TRACKS IN YOUR WORLD</span></div>
        <div><strong>{{ $playlistCount }}</strong><span>PLAYLISTS MADE BY YOU</span></div>
        <div><strong>∞</strong><span>VIBES TO EXPLORE</span></div>
    </div>
</section>

<section class="content-section">
    <div class="section-heading">
        <div><span class="eyebrow">THE LATEST DROPS</span><h2>Fresh from your <em>library</em></h2></div>
    </div>
    @if($latestSongs->count())
        <div class="song-grid">@foreach($latestSongs as $song) @include('partials.song-card', ['song' => $song, 'playlists' => $playlists ?? collect()]) @endforeach</div>
    @else
        <div class="empty-panel"><div class="empty-pixel">♫</div><span class="eyebrow">NOTHING IN THE MIX... YET</span><h3>YOUR MUSIC JOURNEY<br>STARTS HERE.</h3><p>Upload your first track and let the good vibes begin.</p><a class="button button-pink" href="{{ route('upload') }}" data-nav>＋ ADD YOUR FIRST SONG</a></div>
    @endif
</section>

@if($recentSongs->count())
<section class="content-section recent-home"><div class="section-heading"><div><span class="eyebrow">BACK ON REPEAT</span><h2>Recently <em>played</em></h2></div><a href="{{ route('recent') }}" class="see-all" data-nav>VIEW HISTORY ↗</a></div><div class="song-grid">@foreach($recentSongs as $song) @include('partials.song-card', ['song' => $song, 'playlists' => $playlists ?? collect()]) @endforeach</div></section>
@endif
@endsection
