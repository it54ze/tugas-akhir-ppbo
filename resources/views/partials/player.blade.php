<footer class="music-player" id="music-player">
    <audio id="global-audio" preload="metadata"></audio>
    <div class="player-track">
        <div class="player-cover" id="player-cover"><span>♫</span></div>
        <div class="player-track-copy"><strong id="player-title">NOTHING PLAYING</strong><span id="player-artist">Pick a track to start your vibe</span></div>
        <button class="player-like" id="player-like" type="button" aria-label="Like current song" aria-pressed="false"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20.8 8.7c0 5-8.8 10.1-8.8 10.1S3.2 13.7 3.2 8.7A4.7 4.7 0 0 1 12 6.2a4.7 4.7 0 0 1 8.8 2.5Z"/></svg></button>
    </div>
    <div class="player-center">
        <div class="player-buttons">
            <button class="player-control shuffle" id="shuffle-button" type="button" aria-label="Shuffle" title="Shuffle">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M3 7h2c1.5 0 2.5 1 3.3 2.4l5.4 9.2c.8 1.4 1.8 2.4 3.3 2.4h3"/><path d="m17 18.5 3.5 2.5-3.5 2.5"/><path d="M3 17h2c1.5 0 2.5-1 3.3-2.4l5.4-9.2C14.5 4 15.5 3 17 3h3"/><path d="m17 0.5 3.5 2.5L17 5.5"/></svg>
            </button>
            <button class="player-control skip-control" id="previous-button" type="button" aria-label="Previous track" title="Previous track">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M19 5 9 12l10 7"/><path d="M5 5v14"/></svg>
            </button>
            <button class="play-main" id="play-button" type="button" aria-label="Play" title="Play">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M8 6 18 12 8 18Z"/></svg>
            </button>
            <button class="player-control skip-control" id="next-button" type="button" aria-label="Next track" title="Next track">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m5 5 10 7-10 7"/><path d="M19 5v14"/></svg>
            </button>
            <button class="player-control repeat" id="repeat-button" type="button" aria-label="Repeat" title="Repeat">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m17 2 4 4-4 4M3 11V9a3 3 0 0 1 3-3h15M7 22l-4-4 4-4M21 13v2a3 3 0 0 1-3 3H3"/></svg>
            </button>
        </div>
        <div class="progress-line"><span class="time-label" id="current-time">0:00</span><input id="progress" type="range" min="0" max="1000" value="0" aria-label="Song progress"><span class="time-label" id="total-time">0:00</span></div>
    </div>
    <div class="player-extra">
        <span class="volume-label">VOL</span>
        <div class="volume-control">
            <input id="volume" type="range" min="0" max="100" step="1" value="80" aria-label="Volume" aria-valuetext="80%" title="Volume: 80%">
            <output class="volume-value" id="volume-value" for="volume">80%</output>
        </div>
        <button id="mute-button" class="player-control mute-button" type="button" aria-label="Mute" title="Mute"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 10v4h4l5 4V6l-5 4H4Z"/><path d="M16 9a5 5 0 0 1 0 6M18.5 6.5a8.5 8.5 0 0 1 0 11"/></svg></button>
    </div>
</footer>
