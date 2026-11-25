# Catatan Implementasi PBO, SOLID, Database, dan REST API

Dokumen ini menjelaskan lokasi implementasi konsep yang diminta pada VYBETIFY. Buka file yang disebutkan untuk melihat kode konkretnya.

## 1. Pemrograman Berorientasi Objek (PBO)

### Abstraksi

`app/Services/Storage/AbstractLocalMediaStorage.php` menyediakan abstraksi penyimpanan file. Kelas ini menyembunyikan detail penyimpanan lokal seperti pembuatan nama file unik, menyimpan, menghapus, dan membuat URL file. Kode pemakai cukup berinteraksi dengan kontrak storage.

### Inheritance

`LocalAudioStorage` dan `LocalCoverStorage` mewarisi `AbstractLocalMediaStorage`. Keduanya menggunakan cara kerja penyimpanan yang sama, tetapi diarahkan ke folder berbeda (`music` dan `covers`).

### Encapsulation

`SongMetadata` menyimpan atribut sebagai `private readonly` dan menyediakan `fromArray()` serta `toArray()` sebagai jalur terkontrol untuk membentuk metadata. Service juga menyimpan dependency sebagai properti `private readonly`, sehingga tidak diubah dari luar objek.

### Polymorphism

`UploadSongService::storeFile()` menerima tipe `FileStorageInterface`, bukan kelas konkret. Objek `LocalAudioStorage` maupun `LocalCoverStorage` dapat digunakan melalui kontrak yang sama. Pemanggil tidak perlu mengetahui implementasi storage yang sedang dipakai.

## 2. Penerapan SOLID

| Prinsip | Contoh pada project |
|---|---|
| Single Responsibility | Controller mengatur HTTP; Form Request memvalidasi; API Resource membentuk JSON; Service menjalankan use-case. |
| Open/Closed | Implementasi storage dapat ditambah melalui kontrak tanpa mengubah alur `UploadSongService`. |
| Liskov Substitution | `LocalAudioStorage` dan `LocalCoverStorage` dapat menggantikan abstraksi storage yang mereka turunkan dan tetap memenuhi kontrak file storage. |
| Interface Segregation | Pemakai audio bergantung pada `AudioStorageInterface`, sedangkan gambar bergantung pada `CoverStorageInterface`; keduanya merupakan kontrak khusus yang mewarisi operasi file umum. |
| Dependency Inversion | `UploadSongService`, `SavePlaylistService`, dan service penghapusan menerima interface melalui constructor; `AppServiceProvider` mengikat interface ke implementasi lokal. |

## 3. Database MySQL

Database bernama `vybetify_db` dibangun melalui Laravel migrations di `database/migrations`.

- `songs`: metadata lagu, lokasi audio, dan lokasi cover.
- `playlists`: nama, deskripsi, dan lokasi cover playlist.
- `playlist_song`: relasi many-to-many antara playlist dan lagu.
- `liked_songs`: daftar lagu favorit.
- `recently_played`: catatan lagu yang berhasil mulai diputar.

Foreign key memakai cascade delete agar relasi lagu dibersihkan ketika lagu dihapus. Seeder tidak memasukkan data contoh.

## 4. REST API

Endpoint JSON berada di `routes/api.php`, controller API berada di `app/Http/Controllers/Api`, validasi berada di `app/Http/Requests`, dan bentuk output dikelola oleh `app/Http/Resources`.

Alur upload lagu:

1. Browser mengirim `multipart/form-data` ke `POST /api/songs`.
2. `StoreSongRequest` memvalidasi metadata, MIME audio, ukuran audio, dan cover.
3. `SongController` memanggil `UploadSongService`.
4. Service memakai interface storage untuk menyimpan file fisik, lalu membuat record database.
5. `SongResource` mengembalikan JSON berisi metadata dan URL audio/cover.
6. JavaScript memakai URL audio tersebut pada satu elemen HTML5 Audio yang tetap ada ketika konten halaman berpindah.

Daftar endpoint lengkap terdapat pada README utama.

## 5. Git dan GitHub

`.gitignore` mencegah `.env`, dependency, cache, dan file musik pribadi masuk ke repository. Workflow `.github/workflows/tests.yml` menjalankan feature test ketika ada push atau pull request menuju branch `main`. Setelah membuat repository kosong di GitHub, ikuti perintah Git pada README utama untuk melakukan commit dan push.
