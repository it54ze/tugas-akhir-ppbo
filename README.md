# VYBETIFY — Pemutar Musik Lokal

VYBETIFY adalah pustaka musik lokal sekaligus pemutar musik berbasis browser yang dibangun dengan **Laravel 13**, PHP 8.3+, MySQL, Blade, CSS, dan JavaScript murni (vanilla). Tampilannya terinspirasi antarmuka piksel ala 90-an dengan palet warna neon yang tetap terkontrol.

Proyek ini dibuat sebagai **Tugas Akhir Pemrograman Berorientasi Objek (PBO)** dan mendemonstrasikan konsep PBO, prinsip SOLID, REST API, serta basis data relasional dalam satu aplikasi utuh.

> Repository ini hanya berisi kode sumber aplikasi. Folder `vendor/` dan `node_modules/` tidak disertakan, jadi dependensi Composer harus dipasang dulu sebelum aplikasi dijalankan.

## Daftar Isi

- [Fitur](#fitur)
- [Teknologi](#teknologi)
- [Prasyarat](#prasyarat)
- [Instalasi dan Menjalankan Aplikasi](#instalasi-dan-menjalankan-aplikasi)
- [Pengaturan Ukuran Upload](#pengaturan-ukuran-upload)
- [Struktur Proyek](#struktur-proyek)
- [Penerapan PBO dan SOLID](#penerapan-pbo-dan-solid)
- [REST API](#rest-api)
- [Database](#database)
- [Pengujian](#pengujian)
- [Alur Kerja Git dan GitHub](#alur-kerja-git-dan-github)
- [Tim Pengembang](#tim-pengembang)
- [Keterbatasan](#keterbatasan)
- [Arah Desain](#arah-desain)

## Fitur

- Upload file audio **MP3, WAV, dan OGG** langsung dari komputer (maksimal 64 MB secara default).
- Upload cover opsional (**JPG, JPEG, PNG, atau WEBP**, maksimal 8 MB) untuk lagu maupun playlist.
- File media disimpan di public storage Laravel, sedangkan metadata lagu disimpan di MySQL.
- Kontrol pemutaran lengkap: play, pause, lanjut, seek, atur volume, mute, sebelumnya/berikutnya, shuffle, dan repeat.
- Satu elemen HTML5 audio tetap hidup saat pindah halaman (navigasi berbasis `fetch`), jadi musik tidak terputus.
- Jelajahi pustaka dengan tampilan Grid/List, lalu cari berdasarkan judul, artis, album, atau genre.
- Buat, ubah, hapus, dan putar playlist, serta tambah atau hapus lagu di dalamnya.
- Simpan lagu favorit (liked songs) dan riwayat lagu yang baru diputar.
- Hapus lagu sekaligus relasi di database dan file audio/cover-nya.
- Database dimulai dalam keadaan kosong: tidak ada lagu, album, artis, atau playlist bawaan.

## Teknologi

| Bagian | Teknologi |
|---|---|
| Backend | PHP 8.3+, Laravel 13 |
| Database | MySQL 8+ (atau MariaDB yang kompatibel) |
| Frontend | Blade, CSS, JavaScript vanilla |
| Pengujian | PHPUnit 12 (SQLite in-memory saat tes) |
| Lingkungan lokal | Laragon (Windows) |

## Prasyarat

- PHP **8.3 atau lebih baru** (syarat Laravel 13).
- Composer 2.
- MySQL 8+ atau MariaDB yang kompatibel.
- Ekstensi PHP yang dibutuhkan Laravel: Ctype, cURL, DOM, Fileinfo, Filter, Hash, Mbstring, OpenSSL, PCRE, PDO, Session, Tokenizer, dan XML.
- Untuk menjalankan tes: ekstensi `pdo_sqlite`.
- Laragon dengan PHP 8.3+ yang sudah dipilih (jika memakai Windows).

Cek versi lewat Laragon Terminal:

```bash
php -v
composer --version
```

Kalau versi PHP masih di bawah 8.3, ganti dulu ke PHP 8.3+ di Laragon sebelum memasang dependensi.

## Instalasi dan Menjalankan Aplikasi

### Di Laragon (Windows)

1. Clone repository ke folder `www` milik Laragon:

   ```bash
   cd C:\laragon\www
   git clone https://github.com/it54ze/tugas-akhir-ppbo.git
   cd tugas-akhir-ppbo
   ```

   Kalau kamu mendapatkan proyek ini sebagai file ZIP, ekstrak saja ke `C:\laragon\www\tugas-akhir-ppbo`.

2. Nyalakan **MySQL** dari Laragon. Apache tidak wajib kalau kamu memakai `php artisan serve`.

3. Pasang dependensi Laravel:

   ```bash
   composer install
   ```

4. Buat file environment dan application key:

   ```bash
   copy .env.example .env
   php artisan key:generate
   ```

5. Buka phpMyAdmin, lalu buat database kosong bernama `vybetify_db` dengan collation `utf8mb4_unicode_ci`.

6. Periksa pengaturan database di `.env`. Nilai bawaannya:

   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=vybetify_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```

   Isi `DB_PASSWORD` kalau akun root MySQL kamu memakai password.

7. Jalankan migrasi dan buat symbolic link untuk public storage:

   ```bash
   php artisan migrate
   php artisan storage:link
   ```

8. Jalankan server pengembangan:

   ```bash
   php artisan serve
   ```

9. Buka **http://127.0.0.1:8000** di browser.

10. Pilih **Upload Music**, pilih file audio asli, isi judul dan artis (wajib), lalu upload. Lagu baru akan muncul di **Your Library** dan siap diputar.

### Jalan pintas (opsional)

Composer menyediakan skrip `setup` yang menjalankan `composer install`, menyalin `.env.example` ke `.env`, membuat application key, dan menjalankan migrasi:

```bash
composer run setup
```

Database `vybetify_db` tetap harus dibuat lebih dulu, dan `php artisan storage:link` tetap dijalankan manual.

## Pengaturan Ukuran Upload

Batas validasi bawaan aplikasi adalah 64 MB untuk audio dan 8 MB untuk cover. Keduanya bisa diubah lewat `.env`:

```dotenv
VYBETIFY_AUDIO_MAX_KB=65536
VYBETIFY_COVER_MAX_KB=8192
```

PHP juga harus mengizinkan ukuran request sedikit lebih besar daripada batas audio. Ubah di `php.ini` yang aktif di Laragon, misalnya:

```ini
upload_max_filesize = 64M
post_max_size = 70M
max_file_uploads = 20
```

Restart Apache atau proses PHP di terminal setelah mengubah konfigurasi PHP. Batas upload yang berlaku adalah **nilai terkecil** di antara pengaturan PHP, web server, dan validasi Laravel.

## Struktur Proyek

```text
tugas-akhir-ppbo/
├── app/
│   ├── Contracts/                 # Interface storage (Audio, Cover, File)
│   ├── Data/                      # Value object SongMetadata (encapsulation)
│   ├── Http/
│   │   ├── Controllers/           # Controller web dan REST API
│   │   ├── Requests/              # Validasi input (Form Request)
│   │   └── Resources/             # Transformasi respons JSON
│   ├── Models/                    # Model Eloquent
│   ├── Providers/                 # Binding dependency (AppServiceProvider)
│   └── Services/                  # Use-case dan implementasi storage
│       └── Storage/               # AbstractLocalMediaStorage + implementasi lokal
├── bootstrap/                     # Bootstrap aplikasi Laravel
├── config/                        # Konfigurasi app, database, filesystem, vybetify
├── database/
│   ├── factories/                 # Factory untuk Song dan Playlist
│   ├── migrations/                # Skema database relasional
│   └── seeders/                   # Sengaja kosong
├── docs/                          # Catatan PBO, SOLID, database, dan REST API
├── public/
│   ├── css/app.css
│   ├── js/app.js
│   └── images/                    # Aset branding dan ilustrasi arcade
├── resources/views/               # Halaman Blade dan komponen partial
│   ├── layouts/
│   ├── pages/
│   └── partials/                  # player, sidebar, song-card
├── routes/
│   ├── api.php                    # Route REST API (otomatis berprefiks /api)
│   └── web.php                    # Route halaman browser
├── storage/app/public/
│   ├── music/                     # File audio hasil upload
│   └── covers/                    # File cover hasil upload
└── tests/Feature/                 # Feature test
```

Penjelasan lebih detail ada di [`docs/pbo-solid-rest-api.md`](docs/pbo-solid-rest-api.md).

## Penerapan PBO dan SOLID

Kode disusun untuk memperlihatkan konsep PBO yang dipersyaratkan, bukan menumpuk seluruh logika di satu controller besar.

| Konsep | Lokasi penerapan | Penjelasan |
|---|---|---|
| Abstraction | `AbstractLocalMediaStorage`, `SongMetadata`, dan kontrak storage | Perilaku umum penyimpanan media didefinisikan di balik abstract class dan interface. |
| Inheritance | `LocalAudioStorage` dan `LocalCoverStorage` | Kedua kelas storage mewarisi logika penanganan file dari `AbstractLocalMediaStorage`. |
| Encapsulation | Model Eloquent, dependency service, dan properti `private readonly` | State kelas dan dependency service disimpan di balik method dan batas visibilitas. |
| Polymorphism | `UploadSongService::storeFile(FileStorageInterface ...)` serta binding `AudioStorageInterface` / `CoverStorageInterface` | Use-case upload dan hapus memanggil method kontrak tanpa bergantung pada kelas konkret, sehingga implementasi bisa diganti tanpa menulis ulang use-case. |
| Single Responsibility | Controller, Form Request, Resource, dan Service | Controller mengatur alur HTTP, request memvalidasi, resource menyusun JSON, service menjalankan use-case dan penyimpanan file. |
| Open/Closed | Kontrak dan implementasi storage | Implementasi storage baru bisa ditambahkan dan di-bind tanpa mengubah logika service upload. |
| Liskov Substitution | `LocalAudioStorage` dan `LocalCoverStorage` | Setiap subclass memenuhi kontrak storage yang diwarisi dari abstract class. |
| Interface Segregation | `AudioStorageInterface`, `CoverStorageInterface`, `FileStorageInterface` | Pemakai hanya bergantung pada kontrak storage yang relevan, bukan satu interface besar yang tidak terkait. |
| Dependency Inversion | `UploadSongService`, `SavePlaylistService`, dan `DeleteSongService` | Use-case tingkat tinggi bergantung pada interface, lalu `AppServiceProvider` memetakannya ke implementasi storage lokal. |

## REST API

Antarmuka browser memakai endpoint di bawah ini. Respons koleksi maupun data tunggal ditransformasi dengan Laravel API Resource.

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/api/songs` | Daftar lagu; mendukung `?search=` dan `?ids=1,2,3` |
| POST | `/api/songs` | Upload lagu (`multipart/form-data`) |
| GET | `/api/songs/{id}` | Detail lagu |
| DELETE | `/api/songs/{id}` | Hapus lagu beserta file tersimpannya |
| GET | `/api/playlists` | Daftar playlist |
| POST | `/api/playlists` | Buat playlist (multipart; `cover` opsional) |
| GET | `/api/playlists/{id}` | Detail playlist beserta lagunya |
| PUT/PATCH | `/api/playlists/{id}` | Ubah nama/deskripsi playlist |
| DELETE | `/api/playlists/{id}` | Hapus playlist beserta cover-nya (lagu tidak ikut terhapus) |
| POST | `/api/playlists/{id}/songs` | Tambah lagu; body JSON: `{"song_id": 1}` |
| DELETE | `/api/playlists/{id}/songs` | Hapus beberapa lagu sekaligus dari playlist; body JSON: `{"song_ids": [1, 2]}` |
| DELETE | `/api/playlists/{id}/songs/{songId}` | Hapus satu lagu dari playlist |
| GET | `/api/liked-songs` | Daftar lagu yang disukai |
| PUT | `/api/songs/{id}/like` | Sukai lagu |
| DELETE | `/api/songs/{id}/like` | Batalkan suka |
| GET | `/api/recently-played` | Daftar lagu yang baru diputar |
| POST | `/api/songs/{id}/play` | Catat lagu yang berhasil mulai diputar |

### Aturan validasi

| Field | Aturan |
|---|---|
| `title`, `artist` | wajib, maksimal 150 karakter |
| `album` | opsional, maksimal 150 karakter |
| `genre` | opsional, maksimal 80 karakter |
| `duration` | opsional, numerik, minimal 0 |
| `audio` | wajib, MIME MP3/WAV/OGG, ukuran sesuai `VYBETIFY_AUDIO_MAX_KB` |
| `cover` | opsional, gambar JPG/JPEG/PNG/WEBP, ukuran sesuai `VYBETIFY_COVER_MAX_KB` |
| Playlist `name` | wajib, maksimal 100 karakter |
| Playlist `description` | opsional, maksimal 500 karakter |

## Database

Skema dibangun lewat migration Laravel di `database/migrations`.

| Tabel | Isi |
|---|---|
| `songs` | Metadata lagu serta lokasi file audio dan cover |
| `playlists` | Nama, deskripsi, dan lokasi cover playlist |
| `playlist_song` | Relasi many-to-many antara playlist dan lagu |
| `liked_songs` | Referensi lagu favorit (satu lagu hanya bisa disukai sekali) |
| `recently_played` | Riwayat pemutaran lagu |

Foreign key memakai **cascade delete**, jadi saat lagu dihapus, data lagu itu di playlist, daftar suka, dan riwayat ikut terhapus. Tidak ada tabel user karena aplikasi ini dirancang untuk satu pengguna di lingkungan lokal.

## Pengujian

Feature test ada di `tests/Feature` dan mencakup: halaman beranda, database awal tanpa data demo, upload/daftar/suka/hapus lagu, pembuatan playlist dan penambahan lagu, penghapusan lagu terpilih dari playlist tanpa menghapus lagu di pustaka, serta pencatatan riwayat putar.

```bash
composer test
# atau
php artisan test
```

Saat tes berjalan, aplikasi memakai **SQLite in-memory** (diatur di `phpunit.xml`), jadi MySQL tidak perlu aktif untuk menjalankan tes.

Cek sintaks cepat:

```bash
php -l app/Http/Controllers/Api/SongController.php
node --check public/js/app.js
```

Hal-hal yang tetap perlu diuji manual di Laragon target: operasi MySQL sungguhan, decoding audio di browser, batas ukuran upload, dan perilaku pemutaran yang tidak terputus saat pindah halaman.

## Alur Kerja Git dan GitHub

Repository: <https://github.com/it54ze/tugas-akhir-ppbo>

`.gitignore` sudah mengecualikan `.env`, `vendor/`, `node_modules/`, cache, log, serta file musik dan cover yang diupload secara lokal (folder `.gitkeep` tetap dilacak).

Alur kerja tim yang dipakai: setiap bagian dikerjakan di branch `feature/...`, lalu digabung ke `main` lewat Pull Request.

```bash
git checkout -b feature/nama-fitur
git add .
git commit -m "feat: deskripsi singkat perubahan"
git push -u origin feature/nama-fitur
```

Setelah itu buka Pull Request di GitHub dan minta review sebelum merge ke `main`. Jangan pernah commit `.env`, password, API key, atau file musik pribadi.

## Tim Pengembang

Pembagian kerja berdasarkan riwayat commit dan Pull Request di repository:

| Bagian | Branch / PR | Akun GitHub |
|---|---|---|
| Fondasi Laravel dan routing | commit awal | `it54ze` |
| Storage layer (contracts, metadata, storage lokal) | `feature/storage-layer` | `kodingannya-ipi` |
| Migration, model, dan factory | `feature/database-models` | `cikiverse` |
| Song API | `feature/song-api` | `riri` |
| Playlist API | `feature/playlist-api` | `Lylies` |
| Liked, recently played, dan tes | `feature/liked-recent-tests` | `mllyeee` |
| Tampilan frontend (halaman, player, sidebar, song-card) | `feature/frontend-views` | `dimid` |
| Review dan merge Pull Request | semua PR | `Erzsa` |

## Keterbatasan

- Aplikasi ditujukan untuk **satu pengguna di lingkungan lokal**. Jangan dipublikasikan ke internet tanpa menambahkan autentikasi, otorisasi, rate limiting, dan hardening produksi.
- Tidak ada konfigurasi CI (GitHub Actions) di repository saat ini, jadi tes dijalankan manual.
- Google Fonts dimuat dari internet jika tersedia; jika offline, font bawaan sistem dipakai sebagai cadangan.

## Arah Desain

- **Midnight Cosmos** `#0A0D16` sebagai warna dasar.
- Aksen terbatas: Electric Blue `#2F55F5`, Luminous Lime `#39E34A`, Neon Fuchsia `#FF2DAA`, Vivid Tangerine `#FF5622`, dan Cosmic Yellow `#FFF20A`.
- Panel hero berpola grid piksel dan motif geometris yang terinspirasi layar sambutan retro.
- Nuansa pesta neon tanpa membuat setiap komponen menyala.
- `Archivo Black` untuk judul ekspresif, `Press Start 2P` secukupnya untuk label bergaya HUD piksel, dan `Space Grotesk` untuk teks antarmuka yang mudah dibaca.

## Lisensi

MIT (sesuai `composer.json`).
