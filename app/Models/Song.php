<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Song extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'artist', 'album', 'genre', 'duration', 'audio_path', 'cover_path',
    ];

    protected function casts(): array
    {
        return ['duration' => 'decimal:2'];
    }

    public function playlists(): BelongsToMany
    {
        return $this->belongsToMany(Playlist::class, 'playlist_song')->withTimestamps();
    }

    public function historyEntries(): HasMany
    {
        return $this->hasMany(RecentlyPlayed::class);
    }

}
