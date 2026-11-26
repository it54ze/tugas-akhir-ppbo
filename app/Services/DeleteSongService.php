<?php

namespace App\Services;

use App\Contracts\AudioStorageInterface;
use App\Contracts\CoverStorageInterface;
use App\Models\Song;
use Illuminate\Support\Facades\DB;

class DeleteSongService
{
    public function __construct(
        private readonly AudioStorageInterface $audioStorage,
        private readonly CoverStorageInterface $coverStorage,
    ) {
    }

    public function execute(Song $song): void
    {
        $audioPath = $song->audio_path;
        $coverPath = $song->cover_path;

        DB::transaction(fn () => $song->delete());

        $this->audioStorage->delete($audioPath);
        $this->coverStorage->delete($coverPath);
    }
}
