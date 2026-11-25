(() => {
    'use strict';

    const config = window.VYBETIFY || { apiBase: '/api', baseUrl: '/' };
    const audio = document.getElementById('global-audio');
    const state = { queue: [], index: -1, current: null, shuffle: false, repeat: false, busy: false, playlists: [], searchSongs: [], playlistPickerId: null };
    const $ = (selector, context = document) => context.querySelector(selector);
    const $$ = (selector, context = document) => Array.from(context.querySelectorAll(selector));

    function toast(message, type = 'success') {
        const region = $('#toast-region');
        if (!region) return;
        const item = document.createElement('div');
        item.className = `toast ${type}`;
        item.textContent = message;
        region.appendChild(item);
        window.setTimeout(() => item.remove(), 3300);
    }

    async function api(path, options = {}) {
        const headers = { Accept: 'application/json', ...(options.headers || {}) };
        if (!(options.body instanceof FormData) && options.body && !headers['Content-Type']) {
            headers['Content-Type'] = 'application/json';
        }
        const response = await fetch(`${config.apiBase}${path}`, {
            credentials: 'same-origin',
            ...options,
            headers,
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            const firstError = data.errors ? Object.values(data.errors).flat()[0] : null;
            throw new Error(firstError || data.message || 'Ada masalah saat memproses permintaan.');
        }
        return data;
    }

    function formatTime(seconds) {
        if (!Number.isFinite(seconds) || seconds < 0) return '0:00';
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60).toString().padStart(2, '0');
        return `${mins}:${secs}`;
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char]));
    }

    function setActiveNav() {
        const currentPath = window.location.pathname.replace(/\/$/, '') || '/';
        $$('[data-nav]').forEach(link => {
            const linkPath = new URL(link.href, window.location.origin).pathname.replace(/\/$/, '') || '/';
            const active = linkPath === currentPath || (linkPath === '/playlists' && currentPath.startsWith('/playlists'));
            if (link.classList.contains('nav-link')) link.classList.toggle('active', active);
        });
    }

    async function navigate(url, push = true) {
        try {
            const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) throw new Error('Halaman tidak dapat dibuka.');
            const html = await response.text();
            const parsed = new DOMParser().parseFromString(html, 'text/html');
            const nextContent = $('#page-content', parsed);
            const currentContent = $('#page-content');
            if (!nextContent || !currentContent) throw new Error('Konten halaman tidak ditemukan.');
            currentContent.innerHTML = nextContent.innerHTML;
            document.title = parsed.title || 'VYBETIFY';
            if (push) history.pushState({}, '', url);
            window.scrollTo({ top: 0, behavior: 'auto' });
            setActiveNav();
            initializePage();
        } catch (error) {
            toast(error.message, 'error');
        }
    }

    function playerElements() {
        return {
            cover: $('#player-cover'), title: $('#player-title'), artist: $('#player-artist'), like: $('#player-like'),
            play: $('#play-button'), currentTime: $('#current-time'), totalTime: $('#total-time'), progress: $('#progress'),
        };
    }

    function renderPlayer(song) {
        const el = playerElements();
        if (!song) return;
        state.current = song;
        if (el.title) el.title.textContent = song.title || 'UNTITLED TRACK';
        if (el.artist) el.artist.textContent = song.artist || 'Unknown artist';
        if (el.cover) {
            el.cover.innerHTML = song.cover_url ? `<img src="${escapeHtml(song.cover_url)}" alt="">` : '<span>♫</span>';
        }
        if (el.like) {
            el.like.classList.toggle('liked', !!song.is_liked);
            el.like.textContent = '♡';
            el.like.setAttribute('aria-pressed', song.is_liked ? 'true' : 'false');
        }
    }

    const playerIconMarkup = {
        play: '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M8 6 18 12 8 18Z"/></svg>',
        pause: '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M8 6h3v12H8zM14 6h3v12h-3z"/></svg>',
        volume: '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 10v4h4l5 4V6l-5 4H4Z"/><path d="M16 9a5 5 0 0 1 0 6M18.5 6.5a8.5 8.5 0 0 1 0 11"/></svg>',
        muted: '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 10v4h4l5 4V6l-5 4H4Z"/><path d="m17 9 5 6m0-6-5 6"/></svg>',
    };

    function updatePlayButton(playing) {
        const el = playerElements();
        if (el.play) {
            el.play.innerHTML = playing ? playerIconMarkup.pause : playerIconMarkup.play;
            el.play.classList.toggle('is-playing', playing);
            el.play.setAttribute('aria-label', playing ? 'Pause' : 'Play');
            el.play.setAttribute('title', playing ? 'Pause' : 'Play');
        }
    }

    function renderPlaylistPlaybackState() {
        const currentId = Number(state.current?.id);
        const isPlaying = !!state.current && !audio.paused && !audio.ended;

        $$('[data-playlist-track-id]').forEach(row => {
            const isCurrent = isPlaying && Number(row.dataset.playlistTrackId) === currentId;
            row.classList.toggle('is-currently-playing', isCurrent);

            const button = $('[data-play]', row);
            if (!button) return;
            button.innerHTML = isCurrent
                ? '<span class="track-playing-disc" aria-hidden="true"><span></span></span>'
                : '▶';
            button.classList.toggle('is-playing', isCurrent);
            button.setAttribute('aria-label', isCurrent ? 'Now playing' : `Play ${row.dataset.songTitle || 'track'}`);
            button.title = isCurrent ? 'Now playing' : 'Play track';
        });
    }

    function updateSelectedPlaylistSongs() {
        const toolbarButton = $('[data-remove-selected-songs]');
        if (!toolbarButton) return;
        const selectedCount = $$('[data-playlist-track-select]:checked').length;
        toolbarButton.disabled = selectedCount === 0;
        toolbarButton.textContent = `REMOVE SELECTED (${selectedCount})`;
    }

    function updateMuteButton(muted) {
        const button = $('#mute-button');
        if (!button) return;
        button.innerHTML = muted ? playerIconMarkup.muted : playerIconMarkup.volume;
        button.setAttribute('aria-label', muted ? 'Unmute' : 'Mute');
        button.setAttribute('title', muted ? 'Unmute' : 'Mute');
        button.classList.toggle('muted', muted);
    }

    async function recordPlay(song) {
        try {
            await api(`/songs/${song.id}/play`, { method: 'POST' });
        } catch (error) {
            console.warn('Riwayat pemutaran tidak dapat disimpan:', error.message);
        }
    }

    function songFromCard(card) {
        if (!card) return null;
        const id = Number(card.dataset.songCard);
        const audioUrl = card.dataset.audioUrl;
        if (!Number.isInteger(id) || id <= 0 || !audioUrl) return null;

        return {
            id,
            title: card.dataset.songTitle || 'UNTITLED TRACK',
            artist: card.dataset.songArtist || 'Unknown artist',
            audio_url: audioUrl,
            cover_url: card.dataset.coverUrl || null,
            is_liked: card.dataset.isLiked === 'true',
        };
    }

    function playSong(song, queue = null) {
        if (!song || !song.audio_url) {
            toast('File audio tidak ditemukan.', 'error');
            return Promise.resolve();
        }

        // Start playback before awaiting any network request so the browser
        // still recognizes this call as a direct response to the user's click.
        if (queue && queue.length) {
            state.queue = queue;
            state.index = queue.findIndex(item => Number(item.id) === Number(song.id));
            if (state.index < 0) {
                state.queue = [song];
                state.index = 0;
            }
        } else {
            state.queue = [song];
            state.index = 0;
        }

        renderPlayer(song);
        audio.src = song.audio_url;
        audio.load();

        let playRequest;
        try {
            playRequest = audio.play();
        } catch (error) {
            updatePlayButton(false);
            toast('Browser tidak dapat memutar file ini. Coba format MP3, WAV, atau OGG yang valid.', 'error');
            return Promise.resolve();
        }

        const playback = Promise.resolve(playRequest).then(() => {
            updatePlayButton(true);
            recordPlay(song);
        }).catch(() => {
            updatePlayButton(false);
            toast('Browser tidak dapat memutar file ini. Periksa file audio dan format MP3, WAV, atau OGG.', 'error');
        });

        // Load the full library queue in the background. This must not delay
        // audio.play(), otherwise browsers may block playback after a click.
        if (!queue || !queue.length) {
            api('/songs').then(result => {
                const songs = result.data || [];
                if (state.current && Number(state.current.id) === Number(song.id) && songs.length) {
                    const index = songs.findIndex(item => Number(item.id) === Number(song.id));
                    if (index >= 0) {
                        state.queue = songs;
                        state.index = index;
                    }
                }
            }).catch(error => console.warn('Antrean lagu tidak dapat dimuat:', error.message));
        }

        return playback;
    }

    async function playAt(index) {
        if (!state.queue.length) {
            const result = await api('/songs');
            state.queue = result.data || [];
        }
        if (!state.queue.length) return toast('Library masih kosong. Upload lagu dulu, yuk.', 'error');
        if (state.shuffle && state.queue.length > 1) {
            let randomIndex = index;
            while (randomIndex === index) randomIndex = Math.floor(Math.random() * state.queue.length);
            index = randomIndex;
        }
        if (index < 0 || index >= state.queue.length) {
            if (state.repeat) index = 0;
            else return updatePlayButton(false);
        }
        state.index = index;
        await playSong(state.queue[index], state.queue);
    }

    function renderSearchResults(songs) {
        const resultBox = $('#search-results');
        const empty = $('#search-empty');
        const count = $('#search-count');
        if (!resultBox) return;
        state.searchSongs = songs;
        if (count) count.textContent = `${songs.length} TRACK${songs.length === 1 ? '' : 'S'} FOUND`;
        if (!songs.length) {
            resultBox.innerHTML = '';
            if (empty) empty.classList.remove('hidden');
            return;
        }
        if (empty) empty.classList.add('hidden');
        const playlistOptions = state.playlists.map(playlist =>
            `<option value="${Number(playlist.id)}">${escapeHtml(playlist.name)}</option>`
        ).join('');
        resultBox.innerHTML = songs.map((song, index) => `
            <article class="song-card" data-song-card="${song.id}" data-song-title="${escapeHtml(song.title)}" data-song-artist="${escapeHtml(song.artist)}" data-audio-url="${escapeHtml(song.audio_url || '')}" data-cover-url="${escapeHtml(song.cover_url || '')}" data-is-liked="${song.is_liked ? 'true' : 'false'}" tabindex="0" aria-label="Play ${escapeHtml(song.title)}">
                <div class="song-art-wrap">${song.cover_url ? `<img class="song-art" src="${escapeHtml(song.cover_url)}" alt="">` : '<div class="song-art art-placeholder"><span>♫</span><small>VYBE<br>TRACK</small></div>'}<button class="art-play" type="button" data-play="${song.id}">▶</button><span class="art-index">${String(index + 1).padStart(2, '0')}</span></div>
                <div class="song-card-info"><div class="song-title-line"><strong>${escapeHtml(song.title)}</strong><button class="like-button ${song.is_liked ? 'liked' : ''}" type="button" data-like="${song.id}" aria-label="Like ${escapeHtml(song.title)}" aria-pressed="${song.is_liked ? 'true' : 'false'}">♡</button></div><span>${escapeHtml(song.artist)}</span></div>
                <div class="song-card-actions">
                    ${state.playlists.length
                        ? `<select aria-label="Add ${escapeHtml(song.title)} to playlist" data-add-to-playlist="${song.id}"><option value="">＋ Playlist</option>${playlistOptions}</select>`
                        : '<span class="text-action">CREATE A PLAYLIST FIRST</span>'}
                    <button class="text-action delete-song" type="button" data-delete-song="${song.id}">REMOVE</button>
                </div>
            </article>`).join('');
    }

    function setupSearch() {
        const input = $('#music-search');
        if (!input) return;
        // Search cards are generated client-side, so load playlists once here
        // to give them the same Add to Playlist control as the other song cards.
        api('/playlists').then(result => {
            state.playlists = result.data || [];
            if (state.searchSongs.length) renderSearchResults(state.searchSongs);
        }).catch(error => console.warn('Daftar playlist tidak dapat dimuat:', error.message));

        let timer;
        input.addEventListener('input', () => {
            window.clearTimeout(timer);
            timer = window.setTimeout(async () => {
                const term = input.value.trim();
                if (!term) return renderSearchResults([]);
                try {
                    const result = await api(`/songs?search=${encodeURIComponent(term)}`);
                    renderSearchResults(result.data || []);
                } catch (error) { toast(error.message, 'error'); }
            }, 220);
        });
        input.focus({ preventScroll: true });
    }

    function setupUpload() {
        const form = $('#upload-form');
        if (!form) return;
        const audioInput = $('#audio-file');
        const coverInput = $('#cover-file');
        const audioDrop = $('#audio-drop');
        $('#browse-audio')?.addEventListener('click', () => audioInput.click());
        audioInput?.addEventListener('change', () => {
            const file = audioInput.files[0];
            $('#audio-file-name').textContent = file ? `${file.name} · ${(file.size / 1024 / 1024).toFixed(1)} MB` : 'NO FILE SELECTED';
        });
        coverInput?.addEventListener('change', () => {
            const file = coverInput.files[0];
            $('#cover-file-name').textContent = file ? file.name : 'No cover selected';
            if (file) {
                const reader = new FileReader();
                reader.onload = event => { $('#cover-preview').innerHTML = `<img src="${event.target.result}" alt="Cover preview">`; };
                reader.readAsDataURL(file);
            }
        });
        ['dragenter', 'dragover'].forEach(name => audioDrop?.addEventListener(name, event => { event.preventDefault(); audioDrop.classList.add('dragover'); }));
        ['dragleave', 'drop'].forEach(name => audioDrop?.addEventListener(name, event => { event.preventDefault(); audioDrop.classList.remove('dragover'); }));
        audioDrop?.addEventListener('drop', event => {
            const file = event.dataTransfer.files[0];
            if (!file) return;
            const transfer = new DataTransfer(); transfer.items.add(file); audioInput.files = transfer.files;
            audioInput.dispatchEvent(new Event('change'));
        });
        form.addEventListener('submit', async event => {
            event.preventDefault();
            const button = $('#upload-submit');
            if (!audioInput.files.length) return toast('Pilih file audio terlebih dahulu.', 'error');
            button.disabled = true; button.textContent = 'UPLOADING...';
            try {
                const result = await api('/songs', { method: 'POST', body: new FormData(form) });
                toast('Track berhasil di-upload ke library!');
                if (result.data) state.queue.unshift(result.data);
                await navigate(`${config.baseUrl.replace(/\/$/, '')}/library`);
            } catch (error) {
                toast(error.message, 'error');
                button.disabled = false; button.innerHTML = 'UPLOAD TRACK <span>↗</span>';
            }
        });
    }

    function playlistDetailUrl(playlistId) {
        return `${config.baseUrl.replace(/\/$/, '')}/playlists/${Number(playlistId)}`;
    }

    function appendPlaylistCard(playlist) {
        const grid = $('#playlist-grid');
        if (!grid || !playlist?.id) return;
        if ($(`[data-playlist-card="${Number(playlist.id)}"]`, grid)) return;

        $('#playlists-empty-state')?.classList.add('hidden');
        $('#new-playlist-button')?.classList.remove('hidden');
        grid.classList.remove('hidden');

        const card = document.createElement('a');
        card.className = 'playlist-card';
        card.href = playlistDetailUrl(playlist.id);
        card.dataset.nav = '';
        card.dataset.playlistCard = String(playlist.id);
        const artClass = `art-${(Number(playlist.id) % 4) + 1}`;
        const artwork = playlist.cover_url
            ? `<img src="${escapeHtml(playlist.cover_url)}" alt="">`
            : '<span>♫</span><small>VYBE<br>MIXTAPE</small>';
        card.innerHTML = `
            <div class="playlist-art ${artClass}">${artwork}</div>
            <div class="playlist-card-info">
                <strong>${escapeHtml(playlist.name)}</strong>
                <span><span data-playlist-count="${Number(playlist.id)}">0</span> TRACKS · ${escapeHtml(playlist.description || 'CURATED BY YOU')}</span>
            </div>
            <span class="playlist-arrow">↗</span>`;
        grid.prepend(card);
    }

    function updatePlaylistCardCount(playlistId, count) {
        const countElement = $(`[data-playlist-count="${Number(playlistId)}"]`);
        if (countElement) countElement.textContent = String(Number(count) || 0);
        const playlist = state.playlists.find(item => Number(item.id) === Number(playlistId));
        if (playlist) playlist.songs_count = Number(count) || 0;
    }

    async function openNewPlaylistSongPicker(playlist) {
        const panel = $('#new-playlist-song-picker');
        const songList = $('#new-playlist-song-list');
        const title = $('#new-playlist-picker-title');
        const status = $('#new-playlist-picker-status');
        const openLink = $('#new-playlist-open-link');
        if (!panel || !songList || !playlist?.id) return;

        state.playlistPickerId = Number(playlist.id);
        panel.dataset.playlistId = String(playlist.id);
        panel.dataset.songsCount = '0';
        panel.classList.remove('hidden');
        if (title) title.textContent = `ADD TRACKS TO ${playlist.name || 'YOUR PLAYLIST'}`;
        if (status) status.textContent = 'Choose tracks below. Your playlist stays on this page while you add them.';
        if (openLink) openLink.href = playlistDetailUrl(playlist.id);
        songList.innerHTML = '<p class="playlist-add-empty">Loading your library...</p>';

        try {
            const response = await api('/songs');
            const songs = response.data || [];
            if (!songs.length) {
                songList.innerHTML = '<p class="playlist-add-empty">Your library is empty. <a href="' + escapeHtml(`${config.baseUrl.replace(/\/$/, '')}/upload`) + '" data-nav>Upload a track</a> first.</p>';
                return;
            }

            songList.innerHTML = songs.map(song => `
                <article class="new-playlist-song-row" data-new-playlist-song-row="${Number(song.id)}">
                    <div class="new-playlist-song-art">${song.cover_url ? `<img src="${escapeHtml(song.cover_url)}" alt="">` : '♫'}</div>
                    <div class="new-playlist-song-copy"><strong>${escapeHtml(song.title)}</strong><span>${escapeHtml(song.artist || 'Unknown artist')}</span></div>
                    <button class="button button-outline" type="button" data-add-new-playlist-song="${Number(song.id)}" data-playlist-id="${Number(playlist.id)}">＋ ADD</button>
                </article>`).join('');
        } catch (error) {
            songList.innerHTML = `<p class="playlist-add-empty">Library could not be loaded: ${escapeHtml(error.message)}</p>`;
            toast(error.message, 'error');
        }
    }

    function setupPlaylistForm() {
        const openButton = $('#new-playlist-button');
        const emptyButton = $('#empty-new-playlist');
        const form = $('#create-playlist-form');
        const open = () => { form?.classList.remove('hidden'); form?.querySelector('[name="name"]')?.focus(); };
        openButton?.addEventListener('click', open);
        emptyButton?.addEventListener('click', open);
        form?.addEventListener('submit', async event => {
            event.preventDefault();
            const button = form.querySelector('button[type="submit"]');
            if (button) { button.disabled = true; button.textContent = 'CREATING...'; }
            try {
                const response = await api('/playlists', { method: 'POST', body: new FormData(form) });
                const playlist = response.data || response;
                if (!playlist.id) throw new Error('Playlist dibuat, tetapi data playlist tidak ditemukan. Muat ulang halaman untuk memeriksa hasilnya.');

                state.playlists.unshift(playlist);
                appendPlaylistCard(playlist);
                form.reset();
                form.classList.add('hidden');
                toast('Playlist berhasil dibuat. Sekarang pilih lagu untuk ditambahkan!');
                await openNewPlaylistSongPicker(playlist);
                $('#new-playlist-song-picker')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } catch (error) {
                toast(error.message, 'error');
            } finally {
                if (button) { button.disabled = false; button.textContent = 'SAVE PLAYLIST ↗'; }
            }
        });
    }

    function setupLibraryView() {
        const grid = $('#library-grid');
        if (!grid) return;
        $$('.view-toggle').forEach(button => button.addEventListener('click', () => {
            $$('.view-toggle').forEach(item => item.classList.toggle('active', item === button));
            grid.classList.toggle('list-view', button.dataset.view === 'list');
        }));
    }

    function setupPlayer() {
        $('#play-button')?.addEventListener('click', () => {
            if (!state.current) return playAt(0).catch(error => toast(error.message, 'error'));
            if (audio.paused) audio.play().then(() => updatePlayButton(true)).catch(() => toast('Audio belum dapat diputar.', 'error'));
            else audio.pause();
        });
        $('#previous-button')?.addEventListener('click', () => {
            if (audio.currentTime > 3) audio.currentTime = 0;
            else playAt(state.index - 1).catch(error => toast(error.message, 'error'));
        });
        $('#next-button')?.addEventListener('click', () => playAt(state.index + 1).catch(error => toast(error.message, 'error')));
        $('#shuffle-button')?.addEventListener('click', event => { state.shuffle = !state.shuffle; event.currentTarget.classList.toggle('enabled', state.shuffle); });
        $('#repeat-button')?.addEventListener('click', event => { state.repeat = !state.repeat; event.currentTarget.classList.toggle('enabled', state.repeat); });
        const volumeSlider = $('#volume');
        const volumeValue = $('#volume-value');
        const updateVolumeDisplay = () => {
            if (!volumeSlider) return;
            const percentage = Math.min(100, Math.max(0, Math.round(Number(volumeSlider.value) || 0)));
            if (volumeValue) volumeValue.textContent = `${percentage}%`;
            volumeSlider.setAttribute('aria-valuetext', `${percentage}%`);
            volumeSlider.title = `Volume: ${percentage}%`;
            volumeSlider.style.setProperty('--volume-level', `${percentage}%`);
        };
        volumeSlider?.addEventListener('input', event => {
            const percentage = Math.min(100, Math.max(0, Number(event.target.value) || 0));
            audio.volume = percentage / 100;
            audio.muted = false;
            updateMuteButton(false);
            updateVolumeDisplay();
        });
        audio.volume = Number(volumeSlider?.value ?? 80) / 100;
        updateVolumeDisplay();
        updatePlayButton(false);
        updateMuteButton(audio.muted);
        $('#mute-button')?.addEventListener('click', () => { audio.muted = !audio.muted; updateMuteButton(audio.muted); });
        $('#progress')?.addEventListener('input', event => {
            if (Number.isFinite(audio.duration)) audio.currentTime = (Number(event.target.value) / 1000) * audio.duration;
        });
        audio.addEventListener('timeupdate', () => {
            const el = playerElements();
            if (el.currentTime) el.currentTime.textContent = formatTime(audio.currentTime);
            if (el.progress && Number.isFinite(audio.duration) && audio.duration > 0) el.progress.value = String(Math.round(audio.currentTime / audio.duration * 1000));
        });
        audio.addEventListener('loadedmetadata', () => {
            const el = playerElements();
            if (el.totalTime) el.totalTime.textContent = formatTime(audio.duration);
        });
        audio.addEventListener('play', () => { updatePlayButton(true); renderPlaylistPlaybackState(); });
        audio.addEventListener('pause', () => { updatePlayButton(false); renderPlaylistPlaybackState(); });
        audio.addEventListener('ended', () => {
            if (state.repeat) { audio.currentTime = 0; audio.play(); }
            else playAt(state.index + 1).catch(error => toast(error.message, 'error'));
        });
        audio.addEventListener('error', () => {
            updatePlayButton(false);
            renderPlaylistPlaybackState();
            toast('File audio gagal dimuat. Periksa apakah file masih tersedia dan format didukung browser.', 'error');
        });
        $('#player-like')?.addEventListener('click', async () => {
            if (!state.current) return;
            await toggleLike(state.current.id, $('#player-like'));
        });
    }

    async function toggleLike(songId, button) {
        const wasLiked = button.classList.contains('liked') || button.getAttribute('aria-pressed') === 'true';
        try {
            await api(`/songs/${songId}/like`, { method: wasLiked ? 'DELETE' : 'PUT' });
            const liked = !wasLiked;
            button.classList.toggle('liked', liked);
            button.textContent = '♡';
            button.setAttribute('aria-pressed', liked ? 'true' : 'false');
            if (state.current && Number(state.current.id) === Number(songId)) {
                state.current.is_liked = liked;
                const playerLike = $('#player-like');
                playerLike.classList.toggle('liked', liked); playerLike.textContent = '♡';
            }
            toast(liked ? 'Masuk ke Liked Songs.' : 'Dihapus dari Liked Songs.');
        } catch (error) { toast(error.message, 'error'); }
    }

    async function deleteSong(songId) {
        if (!window.confirm('Hapus lagu ini dari library dan hapus file musiknya?')) return;
        try {
            await api(`/songs/${songId}`, { method: 'DELETE' });
            if (state.current && Number(state.current.id) === Number(songId)) {
                audio.pause(); audio.removeAttribute('src'); state.current = null; updatePlayButton(false);
                $('#player-title').textContent = 'NOTHING PLAYING'; $('#player-artist').textContent = 'Pick a track to start your vibe'; $('#player-cover').innerHTML = '<span>♫</span>';
            }
            state.queue = state.queue.filter(song => Number(song.id) !== Number(songId));
            toast('Lagu dan file terkait berhasil dihapus.');
            await navigate(window.location.href, false);
        } catch (error) { toast(error.message, 'error'); }
    }

    function setupGlobalClicks() {
        document.addEventListener('click', async event => {
            const nav = event.target.closest('a[data-nav]');
            if (nav && nav.origin === window.location.origin && !event.ctrlKey && !event.metaKey && !event.shiftKey && nav.target !== '_blank') {
                event.preventDefault();
                if (nav.href !== window.location.href) await navigate(nav.href);
                return;
            }
            const play = event.target.closest('[data-play]');
            if (play) {
                const card = play.closest('[data-song-card]');
                const cardSong = songFromCard(card);
                if (cardSong) {
                    await playSong(cardSong);
                } else {
                    try {
                        const id = Number(play.dataset.play);
                        const result = await api(`/songs/${id}`);
                        await playSong(result.data || result);
                    } catch (error) { toast(error.message, 'error'); }
                }
                return;
            }
            const like = event.target.closest('[data-like]');
            if (like) { event.preventDefault(); await toggleLike(like.dataset.like, like); return; }
            const remove = event.target.closest('[data-delete-song]');
            if (remove) { await deleteSong(remove.dataset.deleteSong); return; }
            const addNewPlaylistSong = event.target.closest('[data-add-new-playlist-song]');
            if (addNewPlaylistSong) {
                const playlistId = Number(addNewPlaylistSong.dataset.playlistId);
                const songId = Number(addNewPlaylistSong.dataset.addNewPlaylistSong);
                if (!Number.isInteger(playlistId) || playlistId < 1 || !Number.isInteger(songId) || songId < 1) return;
                addNewPlaylistSong.disabled = true;
                addNewPlaylistSong.textContent = 'ADDING...';
                try {
                    const result = await api(`/playlists/${playlistId}/songs`, {
                        method: 'POST',
                        body: JSON.stringify({ song_id: songId }),
                    });
                    const data = result.data || {};
                    addNewPlaylistSong.textContent = data.already_added ? 'ALREADY ADDED' : '✓ ADDED';
                    addNewPlaylistSong.classList.add('is-added');
                    updatePlaylistCardCount(playlistId, data.songs_count);
                    const panel = $('#new-playlist-song-picker');
                    if (panel && Number(panel.dataset.playlistId) === playlistId) {
                        panel.dataset.songsCount = String(data.songs_count ?? (Number(panel.dataset.songsCount || 0) + 1));
                        const status = $('#new-playlist-picker-status');
                        if (status) status.textContent = `${panel.dataset.songsCount} track(s) added. You can keep choosing songs or open the playlist.`;
                    }
                    toast(result.message || 'Lagu berhasil ditambahkan ke playlist.');
                } catch (error) {
                    addNewPlaylistSong.disabled = false;
                    addNewPlaylistSong.textContent = '＋ ADD';
                    toast(error.message || 'Lagu gagal ditambahkan ke playlist.', 'error');
                }
                return;
            }

            const removeSelected = event.target.closest('[data-remove-selected-songs]');
            if (removeSelected) {
                const playlistId = Number(removeSelected.dataset.removeSelectedSongs);
                const selectedSongIds = $$('[data-playlist-track-select]:checked').map(input => Number(input.value)).filter(id => Number.isInteger(id) && id > 0);
                if (!selectedSongIds.length) return toast('Pilih minimal satu lagu untuk dihapus.', 'error');
                if (!window.confirm(`Hapus ${selectedSongIds.length} lagu terpilih dari playlist? File lagu tetap ada di library.`)) return;
                removeSelected.disabled = true;
                try {
                    const result = await api(`/playlists/${playlistId}/songs`, {
                        method: 'DELETE',
                        body: JSON.stringify({ song_ids: selectedSongIds }),
                    });
                    toast(result.message || 'Lagu terpilih berhasil dihapus dari playlist.');
                    await navigate(window.location.href, false);
                } catch (error) {
                    toast(error.message || 'Lagu terpilih gagal dihapus.', 'error');
                    removeSelected.disabled = false;
                }
                return;
            }

            const playList = event.target.closest('[data-play-list]');
            if (playList) {
                const ids = playList.dataset.playList.split(',').map(Number).filter(Boolean);
                if (!ids.length) return toast('Playlist ini belum memiliki lagu.', 'error');
                try {
                    const songs = await api(`/songs?ids=${ids.join(',')}`);
                    const queue = songs.data || [];
                    if (queue.length) await playSong(queue[0], queue);
                } catch (error) { toast(error.message, 'error'); }
                return;
            }
            const deletePlaylist = event.target.closest('[data-delete-playlist]');
            if (deletePlaylist) {
                if (!confirm('Hapus playlist ini? File lagu tidak akan dihapus.')) return;
                try { await api(`/playlists/${deletePlaylist.dataset.deletePlaylist}`, { method: 'DELETE' }); toast('Playlist dihapus.'); await navigate(`${config.baseUrl.replace(/\/$/, '')}/playlists`); }
                catch (error) { toast(error.message, 'error'); }
                return;
            }
            const editPlaylist = event.target.closest('[data-edit-playlist]');
            if (editPlaylist) {
                const name = prompt('Nama playlist:', editPlaylist.dataset.name || '');
                if (name === null || !name.trim()) return;
                const description = prompt('Deskripsi playlist:', editPlaylist.dataset.description || '') ?? '';
                try { await api(`/playlists/${editPlaylist.dataset.editPlaylist}`, { method: 'PATCH', body: JSON.stringify({ name, description }) }); toast('Detail playlist diperbarui.'); await navigate(window.location.href, false); }
                catch (error) { toast(error.message, 'error'); }
                return;
            }
            const removeSong = event.target.closest('[data-remove-playlist-song]');
            if (removeSong) {
                try { await api(`/playlists/${removeSong.dataset.playlistId}/songs/${removeSong.dataset.removePlaylistSong}`, { method: 'DELETE' }); toast('Lagu dihapus dari playlist.'); await navigate(window.location.href, false); }
                catch (error) { toast(error.message, 'error'); }
                return;
            }

            const songCard = event.target.closest('[data-song-card]');
            const clickedControl = event.target.closest('button, a, input, select, textarea, label, [data-no-card-play]');
            if (songCard && !clickedControl) {
                const song = songFromCard(songCard);
                if (song) await playSong(song);
                else toast('Data audio lagu ini belum tersedia. Muat ulang halaman dan coba lagi.', 'error');
            }
        });
        document.addEventListener('keydown', async event => {
            if (event.key !== 'Enter' && event.key !== ' ') return;
            const songCard = event.target.closest('[data-song-card]');
            if (!songCard || event.target !== songCard) return;
            event.preventDefault();
            const song = songFromCard(songCard);
            if (song) await playSong(song);
        });
        document.addEventListener('change', async event => {
            const trackSelection = event.target.closest('[data-playlist-track-select]');
            if (trackSelection) {
                updateSelectedPlaylistSongs();
                return;
            }

            const select = event.target.closest('select[data-add-to-playlist]');
            if (!select || !select.value) return;

            const playlistId = Number(select.value);
            const songId = Number(select.dataset.addToPlaylist);
            if (!Number.isInteger(playlistId) || playlistId < 1 || !Number.isInteger(songId) || songId < 1) {
                toast('Pilih playlist dan lagu yang valid.', 'error');
                select.value = '';
                return;
            }

            select.disabled = true;
            try {
                const result = await api(`/playlists/${playlistId}/songs`, {
                    method: 'POST',
                    body: JSON.stringify({ song_id: songId }),
                });
                toast(result.message || 'Lagu berhasil ditambahkan ke playlist.');
                select.value = '';
            } catch (error) {
                toast(error.message || 'Lagu gagal ditambahkan ke playlist.', 'error');
                select.value = '';
            } finally {
                select.disabled = false;
            }
        });

        document.addEventListener('submit', async event => {
            const form = event.target.closest('form[data-playlist-add-form]');
            if (!form) return;
            event.preventDefault();

            const playlistId = Number(form.dataset.playlistId);
            const songId = Number(form.querySelector('[name="song_id"]')?.value);
            const button = form.querySelector('button[type="submit"]');
            if (!Number.isInteger(playlistId) || playlistId < 1 || !Number.isInteger(songId) || songId < 1) {
                toast('Pilih lagu terlebih dahulu.', 'error');
                return;
            }

            if (button) button.disabled = true;
            try {
                const result = await api(`/playlists/${playlistId}/songs`, {
                    method: 'POST',
                    body: JSON.stringify({ song_id: songId }),
                });
                toast(result.message || 'Lagu berhasil ditambahkan ke playlist.');
                await navigate(window.location.href, false);
            } catch (error) {
                toast(error.message || 'Lagu gagal ditambahkan ke playlist.', 'error');
            } finally {
                if (button) button.disabled = false;
            }
        });
    }

    function initializePage() {
        setupSearch();
        setupUpload();
        setupPlaylistForm();
        setupLibraryView();
        setActiveNav();
        renderPlaylistPlaybackState();
        updateSelectedPlaylistSongs();
    }

    window.addEventListener('popstate', () => navigate(window.location.href, false));
    setupPlayer();
    setupGlobalClicks();
    initializePage();
})();
