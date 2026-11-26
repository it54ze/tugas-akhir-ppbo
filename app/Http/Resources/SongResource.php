<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SongResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'artist' => $this->artist,
            'album' => $this->album,
            'genre' => $this->genre,
            'duration' => $this->duration,
            'audio_url' => Storage::disk('public')->url($this->audio_path),
            'cover_url' => $this->cover_path ? Storage::disk('public')->url($this->cover_path) : null,
            'created_at' => $this->created_at?->toISOString(),
            'is_liked' => $this->when(
                $request->attributes->get('liked_song_ids') !== null,
                fn () => in_array($this->id, $request->attributes->get('liked_song_ids', []), true),
            ),
        ];
    }
}
