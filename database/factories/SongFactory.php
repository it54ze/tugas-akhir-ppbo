<?php

namespace Database\Factories;

use App\Models\Song;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Song>
 */
class SongFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title'      => fake()->sentence(3),
            'artist'     => fake()->name(),
            'album'      => fake()->optional()->words(2, true),
            'genre'      => fake()->randomElement(['Pop', 'Rock', 'Jazz', 'Lo-fi']),
            'duration'   => fake()->randomFloat(2, 120, 360),
            'audio_path' => 'music/' . fake()->uuid() . '.mp3',
            'cover_path' => null,
        ];
    }
}
