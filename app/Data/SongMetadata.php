<?php

namespace App\Data;

final class SongMetadata
{
    private function __construct(
        private readonly string $title,
        private readonly string $artist,
        private readonly ?string $album,
        private readonly ?string $genre,
        private readonly ?float $duration,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            title: trim((string) $data['title']),
            artist: trim((string) $data['artist']),
            album: isset($data['album']) && trim((string) $data['album']) !== '' ? trim((string) $data['album']) : null,
            genre: isset($data['genre']) && trim((string) $data['genre']) !== '' ? trim((string) $data['genre']) : null,
            duration: isset($data['duration']) ? (float) $data['duration'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'artist' => $this->artist,
            'album' => $this->album,
            'genre' => $this->genre,
            'duration' => $this->duration,
        ];
    }
}
