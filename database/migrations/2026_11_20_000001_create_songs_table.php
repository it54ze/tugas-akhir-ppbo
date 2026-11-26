<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->string('artist', 150);
            $table->string('album', 150)->nullable();
            $table->string('genre', 80)->nullable();
            $table->decimal('duration', 10, 2)->nullable();
            $table->string('audio_path');
            $table->string('cover_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('songs');
    }
};
