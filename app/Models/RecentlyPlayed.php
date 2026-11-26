<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecentlyPlayed extends Model
{
    protected $table = 'recently_played';
    public $timestamps = false;

    protected $fillable = ['song_id', 'played_at'];

    protected function casts(): array
    {
        return ['played_at' => 'datetime'];
    }

    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class);
    }
}
