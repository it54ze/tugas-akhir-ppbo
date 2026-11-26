<aside class="sidebar">
    <a href="{{ route('home') }}" class="brand" data-nav aria-label="VYBETIFY home">
        <img class="brand-logo" src="{{ asset('images/branding/vybetify-headphones.webp') }}" alt="">
        <span class="brand-wordmark">VYBE<span class="brand-pink">TIFY</span></span>
    </a>
    <div class="side-label">YOUR SPACE</div>
    <nav class="main-nav">
        <a href="{{ route('home') }}" data-nav data-route="home" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}"><span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/></svg></span> Home</a>
        <a href="{{ route('search') }}" data-nav data-route="search" class="nav-link {{ request()->routeIs('search') ? 'active' : '' }}"><span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 5 5"/></svg></span> Search</a>
        <a href="{{ route('library') }}" data-nav data-route="library" class="nav-link {{ request()->routeIs('library') ? 'active' : '' }}"><span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 4h16v16H4z"/><path d="M8 4v16M11 8h6M11 12h6M11 16h6"/></svg></span> Your Library</a>
        <a href="{{ route('upload') }}" data-nav data-route="upload" class="nav-link {{ request()->routeIs('upload') ? 'active' : '' }}"><span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 4v16M4 12h16"/></svg></span> Upload Music</a>
    </nav>

    <div class="side-label side-label-spaced">YOUR COLLECTION</div>
    <nav class="main-nav compact-nav">
        <a href="{{ route('liked') }}" data-nav data-route="liked" class="nav-link {{ request()->routeIs('liked') ? 'active' : '' }}"><span class="nav-icon heart-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20.8 8.7c0 5-8.8 10.1-8.8 10.1S3.2 13.7 3.2 8.7A4.7 4.7 0 0 1 12 6.2a4.7 4.7 0 0 1 8.8 2.5Z"/></svg></span> Liked Songs</a>
        <a href="{{ route('recent') }}" data-nav data-route="recent" class="nav-link {{ request()->routeIs('recent') ? 'active' : '' }}"><span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.5"/><path d="M12 7v5l3.5 2"/></svg></span> Recently Played</a>
        <a href="{{ route('playlists.index') }}" data-nav data-route="playlists" class="nav-link {{ request()->routeIs('playlists.*') ? 'active' : '' }}"><span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5h16v14H4z"/><path d="M8 5v14M12 9h5M12 13h5"/></svg></span> Playlists</a>
    </nav>
    @if(($sidebarPlaylists ?? collect())->isNotEmpty())
        <div class="sidebar-playlist-list" aria-label="Your playlists">
            @foreach($sidebarPlaylists as $sidebarPlaylist)
                <a href="{{ route('playlists.show', $sidebarPlaylist) }}" data-nav class="sidebar-playlist-link {{ request()->routeIs('playlists.show') && request()->route('playlist')?->id === $sidebarPlaylist->id ? 'active' : '' }}">{{ $sidebarPlaylist->name }}</a>
            @endforeach
        </div>
    @endif

    <div class="sidebar-bottom">
        <div class="sidebar-sticker">
            <span class="sticker-kicker">YOUR PERSONAL MIX</span>
            <strong>MAKE<br><em>NOISE.</em></strong>
            <small>GOOD MUSIC. GOOD MOOD.<br>ALL YOURS.</small>
            <img class="sidebar-sticker-mascot" src="{{ asset('images/arcade/pixel-cat-yawn.webp') }}" alt="Pixel cat mascot" aria-hidden="true">
        </div>
        <div class="sidebar-footer"><span class="live-indicator"></span> RUNNING LOCALLY <span class="version">v1.0</span></div>
    </div>
</aside>
