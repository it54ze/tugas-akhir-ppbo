<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MusicApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_uploaded_song_can_be_listed_liked_and_deleted(): void
    {
        $upload = $this->post('/api/songs', [
            'title' => 'My First Track',
            'artist' => 'Local Artist',
            'album' => 'Home Studio',
            'genre' => 'Electronic',
            'audio' => UploadedFile::fake()->create('first-track.mp3', 128, 'audio/mpeg'),
        ], ['Accept' => 'application/json'])->assertCreated()->assertJsonPath('data.title', 'My First Track');

        $songId = $upload->json('data.id');

        $this->assertDatabaseHas('songs', ['id' => $songId, 'artist' => 'Local Artist']);
        $this->assertCount(1, Storage::disk('public')->allFiles('music'));

        $this->putJson("/api/songs/{$songId}/like")->assertOk();
        $this->assertDatabaseHas('liked_songs', ['song_id' => $songId]);

        $this->getJson('/api/songs')->assertOk()->assertJsonPath('data.0.is_liked', true);

        $this->deleteJson("/api/songs/{$songId}")->assertOk();
        $this->assertDatabaseMissing('songs', ['id' => $songId]);
        $this->assertDatabaseMissing('liked_songs', ['song_id' => $songId]);
        $this->assertCount(0, Storage::disk('public')->allFiles('music'));
    }

    public function test_playlist_can_be_created_and_song_added_through_rest_api(): void
    {
        $songUpload = $this->post('/api/songs', [
            'title' => 'Playlist Track',
            'artist' => 'Test Artist',
            'audio' => UploadedFile::fake()->create('playlist-track.mp3', 64, 'audio/mpeg'),
        ], ['Accept' => 'application/json'])->assertCreated();
        $songId = $songUpload->json('data.id');

        $playlistId = $this->postJson('/api/playlists', [
            'name' => 'Night Drive',
            'description' => 'A personal mix.',
        ])->assertCreated()->json('data.id');

        $this->postJson("/api/playlists/{$playlistId}/songs", ['song_id' => $songId])
            ->assertOk()
            ->assertJsonPath('data.playlist_id', $playlistId)
            ->assertJsonPath('data.song_id', $songId)
            ->assertJsonPath('data.already_added', false);

        // Repeating the same add request must not duplicate the pivot row.
        $this->postJson("/api/playlists/{$playlistId}/songs", ['song_id' => $songId])
            ->assertOk()
            ->assertJsonPath('data.already_added', true);

        $this->assertDatabaseCount('playlist_song', 1);
        $this->getJson("/api/playlists/{$playlistId}")
            ->assertOk()
            ->assertJsonPath('data.name', 'Night Drive')
            ->assertJsonPath('data.songs.0.id', $songId);
    }

    public function test_selected_playlist_songs_can_be_removed_without_deleting_library_tracks(): void
    {
        $firstSong = $this->post('/api/songs', [
            'title' => 'Keep in Library One',
            'artist' => 'Test Artist',
            'audio' => UploadedFile::fake()->create('keep-one.mp3', 64, 'audio/mpeg'),
        ], ['Accept' => 'application/json'])->assertCreated();
        $secondSong = $this->post('/api/songs', [
            'title' => 'Keep in Library Two',
            'artist' => 'Test Artist',
            'audio' => UploadedFile::fake()->create('keep-two.mp3', 64, 'audio/mpeg'),
        ], ['Accept' => 'application/json'])->assertCreated();

        $firstSongId = $firstSong->json('data.id');
        $secondSongId = $secondSong->json('data.id');
        $playlistId = $this->postJson('/api/playlists', ['name' => 'Selected Removal'])
            ->assertCreated()
            ->json('data.id');

        $this->postJson("/api/playlists/{$playlistId}/songs", ['song_id' => $firstSongId])->assertOk();
        $this->postJson("/api/playlists/{$playlistId}/songs", ['song_id' => $secondSongId])->assertOk();

        $this->deleteJson("/api/playlists/{$playlistId}/songs", ['song_ids' => [$firstSongId]])
            ->assertOk()
            ->assertJsonPath('data.playlist_id', $playlistId)
            ->assertJsonPath('data.removed_count', 1)
            ->assertJsonPath('data.songs_count', 1);

        $this->assertDatabaseMissing('playlist_song', [
            'playlist_id' => $playlistId,
            'song_id' => $firstSongId,
        ]);
        $this->assertDatabaseHas('playlist_song', [
            'playlist_id' => $playlistId,
            'song_id' => $secondSongId,
        ]);
        $this->assertDatabaseHas('songs', ['id' => $firstSongId]);
        $this->assertDatabaseHas('songs', ['id' => $secondSongId]);
    }

    public function test_playing_a_song_records_it_in_recent_history(): void
    {
        $upload = $this->post('/api/songs', [
            'title' => 'Recently Played Track',
            'artist' => 'Test Artist',
            'audio' => UploadedFile::fake()->create('recently-played.mp3', 64, 'audio/mpeg'),
        ], ['Accept' => 'application/json'])->assertCreated();

        $songId = $upload->json('data.id');

        $this->postJson("/api/songs/{$songId}/play")
            ->assertOk()
            ->assertJsonPath('message', 'Riwayat pemutaran dicatat.');

        $this->assertDatabaseHas('recently_played', ['song_id' => $songId]);
        $this->getJson('/api/recently-played')
            ->assertOk()
            ->assertJsonPath('data.0.id', $songId);
    }

    public function test_database_starts_without_demo_music_or_playlists(): void
    {
        $this->assertDatabaseCount('songs', 0);
        $this->assertDatabaseCount('playlists', 0);
        $this->assertDatabaseCount('recently_played', 0);
        $this->assertDatabaseCount('liked_songs', 0);
    }
}
