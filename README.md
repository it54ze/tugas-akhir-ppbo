# VYBETIFY — Local Music Player

VYBETIFY is a local music library and browser-based player built with **Laravel 13**, PHP 8.3+, MySQL, Blade, CSS, and vanilla JavaScript. It is inspired by a 90s pixel interface and a controlled neon-party palette.

> This repository contains application source code. Install Composer dependencies on your computer before running Laravel. The current archive does not bundle `vendor/` or Node modules.

## What it does

- Upload real MP3, WAV, and OGG files from your computer (64 MB maximum by default).
- Optionally upload JPG, JPEG, PNG, or WEBP cover art for songs and playlists (8 MB maximum).
- Store media in Laravel's local public storage and song metadata in MySQL.
- Play, pause, resume, seek, change volume, mute, previous/next, shuffle, and repeat.
- Keep the single HTML5 audio element alive while app pages change through fetch-based navigation.
- Browse the library, switch Grid/List view, and search by title, artist, album, or genre.
- Create, edit, delete, and play playlists; add or remove tracks.
- Save liked songs and recently played tracks.
- Delete a song's database relationships and its audio/cover files.
- Start with an empty database. There are no seeded songs, albums, artists, or playlists.

## Requirements

- PHP **8.3 or newer** (Laravel 13 requirement).
- Composer 2.
- MySQL 8+ or a compatible MariaDB version.
- PHP extensions required by Laravel, including Ctype, cURL, DOM, Fileinfo, Filter, Hash, Mbstring, OpenSSL, PCRE, PDO, Session, Tokenizer, and XML.
- Laragon with a PHP 8.3+ version selected.

Check in Laragon Terminal:

```bash
php -v
composer --version
```

If PHP is below 8.3, switch Laragon to a PHP 8.3+ build before installing dependencies.

## Run on Laragon (Windows)

1. Extract this project into `C:\laragon\www\vybetify-laravel`.
2. Start MySQL from Laragon. Apache is optional when using `php artisan serve`.
3. Open Laragon Terminal and enter the project directory:

   ```bash
   cd C:\laragon\www\vybetify-laravel
   ```

4. Install the Laravel packages:

   ```bash
   composer install
   ```

5. Create the environment file and application key:

   ```bash
   copy .env.example .env
   php artisan key:generate
   ```

6. Open phpMyAdmin and create an empty database named `vybetify_db` using `utf8mb4` / `utf8mb4_unicode_ci`.
7. Check `.env` database settings. The default is:

   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=vybetify_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```

   Set `DB_PASSWORD` if your local MySQL root account has a password.

8. Run migrations and create the public storage link:

   ```bash
   php artisan migrate
   php artisan storage:link
   ```

9. Start the Laravel development server:

   ```bash
   php artisan serve
   ```

10. Open **http://127.0.0.1:8000**.
11. Choose **Upload Music**, select a real audio file, complete the required title and artist fields, and upload it. The new track should appear in Your Library and become playable.

### Upload size settings

The default application validation limits are 64 MB for audio and 8 MB for cover images. They can be changed with `VYBETIFY_AUDIO_MAX_KB` and `VYBETIFY_COVER_MAX_KB` in `.env`. PHP must allow the request body to be slightly larger than the audio limit. In the active `php.ini` used by Laragon, adjust for example:

```ini
upload_max_filesize = 64M
post_max_size = 70M
max_file_uploads = 20
```

Restart Apache / the terminal PHP process after changing PHP configuration. The effective upload limit is the smallest value among PHP, web server, and Laravel validation settings.

## Project structure

```text
vybetify-laravel/
├── app/
│   ├── Contracts/                 # Storage interfaces
│   ├── Data/                      # Encapsulated song metadata value object
│   ├── Http/
│   │   ├── Controllers/            # Web and REST API controllers
│   │   ├── Requests/               # Input validation
│   │   └── Resources/              # JSON response transformation
│   ├── Models/                     # Eloquent models
│   ├── Providers/                  # Dependency bindings
│   └── Services/                   # Use-cases and storage implementations
├── bootstrap/                      # Laravel application bootstrap
├── config/                         # App, DB, filesystem, session config
├── database/
│   ├── migrations/                 # Relational database schema
│   └── seeders/                    # Intentionally empty
├── public/
│   ├── css/app.css
│   └── js/app.js
├── resources/views/                # Blade pages and shared UI components
├── routes/
│   ├── api.php                     # REST API routes, auto-prefixed /api
│   └── web.php                     # Browser routes
├── storage/app/public/
│   ├── music/                      # Uploaded audio files
│   └── covers/                     # Uploaded cover art
├── docs/                           # PBO, SOLID, database, and API explanation
├── .github/workflows/               # GitHub Actions tests
└── tests/                           # Feature tests
```

## Object-oriented programming and SOLID mapping

The code is structured to demonstrate the required object-oriented concepts rather than placing the application logic in one large controller.

| Requirement | Where it is applied | Explanation |
|---|---|---|
| Abstraction | `AbstractLocalMediaStorage`, `SongMetadata`, and storage contracts | Common media-storage behavior is defined behind an abstract class and interfaces. |
| Inheritance | `LocalAudioStorage` and `LocalCoverStorage` | Both concrete storage classes inherit shared file-handling behavior from `AbstractLocalMediaStorage`. |
| Encapsulation | Eloquent models, service dependencies, and `private readonly` properties | Class state and service dependencies are kept behind class methods and visibility boundaries. |
| Polymorphism | `UploadSongService::storeFile(FileStorageInterface ...)` and the `AudioStorageInterface` / `CoverStorageInterface` bindings | The upload/delete use-cases call contract methods without depending on concrete storage classes. The implementation can be replaced without rewriting the use-case. |
| Single Responsibility | Controllers, Form Requests, Resources, and Services | Controllers handle HTTP flow; requests validate; resources serialize; services perform application use-cases and file storage. |
| Open/Closed | Storage contracts and implementations | A new storage implementation can be introduced and bound without changing upload service logic. |
| Liskov Substitution | `LocalAudioStorage` and `LocalCoverStorage` | Each subclass honors the shared storage contract inherited from the abstract base class. |
| Interface Segregation | `AudioStorageInterface`, `CoverStorageInterface`, `FileStorageInterface` | Consumers depend on small, relevant file-storage contracts instead of a broad unrelated interface. |
| Dependency Inversion | `UploadSongService`, `SavePlaylistService`, and `DeleteSongService` | High-level use-cases depend on interfaces; `AppServiceProvider` maps them to local storage implementations. |

## REST API

The browser interface uses the REST endpoints below. JSON responses for collections and single records are transformed with Laravel API Resources.

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/api/songs` | List songs; supports `?search=` and `?ids=1,2,3` |
| POST | `/api/songs` | Upload a song (`multipart/form-data`) |
| GET | `/api/songs/{id}` | Get song information |
| DELETE | `/api/songs/{id}` | Delete song and stored files |
| GET | `/api/playlists` | List playlists |
| POST | `/api/playlists` | Create playlist (multipart form data; optional `cover`) |
| GET | `/api/playlists/{id}` | Get playlist and its songs |
| PUT/PATCH | `/api/playlists/{id}` | Update playlist name/description |
| DELETE | `/api/playlists/{id}` | Delete a playlist and its cover (not its songs) |
| POST | `/api/playlists/{id}/songs` | Add a song; JSON body: `{"song_id": 1}` |
| DELETE | `/api/playlists/{id}/songs/{songId}` | Remove a song from a playlist |
| GET | `/api/liked-songs` | List liked tracks |
| PUT | `/api/songs/{id}/like` | Like a song |
| DELETE | `/api/songs/{id}/like` | Unlike a song |
| GET | `/api/recently-played` | List recently played songs |
| POST | `/api/songs/{id}/play` | Record a successful playback start |

### Database tables

- `songs`: uploaded track metadata and stored file paths.
- `playlists`: user-created playlist information and optional cover path.
- `playlist_song`: many-to-many playlist/song relationship.
- `liked_songs`: favorite song references.
- `recently_played`: playback history records.

Foreign keys cascade when songs are deleted so a removed song is also removed from playlists, likes, and history. No user/account table is required for this single-user local application.

## Git and GitHub workflow

The source includes `.gitignore` and a GitHub Actions workflow at `.github/workflows/tests.yml`. It excludes `.env`, dependencies, cache, and uploaded private/local music files. To track the project and connect it to a new GitHub repository, create an empty repository on GitHub (do not add a README there), then run from the project folder:

```bash
git init -b main
git add .
git commit -m "Initial VYBETIFY Laravel 13 project"
git remote add origin https://github.com/USERNAME/vybetify-laravel.git
git branch -M main
git push -u origin main
```

Replace `USERNAME` with your GitHub username. Do not commit `.env`, passwords, API keys, or private music files. For normal development:

```bash
git status
git add .
git commit -m "Add VYBETIFY music player feature"
git push
```

## Testing and limitations

- The repository includes homepage and REST API feature tests for empty initial data, upload, likes, deletion, and playlist operations. Run them after installing dependencies with `php artisan test`.
- GitHub Actions runs the automated feature tests for commits and pull requests targeting `main`.
- PHP syntax and JavaScript syntax can be checked with `php -l` and `node --check`.
- Actual MySQL operations, browser audio decoding, file upload limits, and the browser's persistent playback behavior must be tested on the target Laragon installation.
- Google Fonts are loaded from Google Fonts when internet access is available; system fallback fonts are included.
- This project is intended for a single-user local environment. Do not expose the app publicly without adding authentication, authorization, rate limiting, and production hardening.

## Design direction

- Midnight Cosmos `#0A0D16` as the base.
- Electric Blue `#2F55F5`, Luminous Lime `#39E34A`, Neon Fuchsia `#FF2DAA`, Vivid Tangerine `#FF5622`, and Cosmic Yellow `#FFF20A` as restrained accents.
- Pixel-grid hero panel and geometric motifs inspired by the retro welcome screen.
- Neon-party energy without making every component glow.
- `Archivo Black` for expressive headings, a limited `Press Start 2P` accent for pixel HUD labels, and `Space Grotesk` for legible interface text.
