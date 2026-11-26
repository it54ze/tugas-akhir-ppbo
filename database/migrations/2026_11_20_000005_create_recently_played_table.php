<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('recently_played', function (Blueprint $table) {
            $table->id();
            $table->foreignId('song_id')->constrained()->cascadeOnDelete();
            $table->timestamp('played_at')->useCurrent();
            $table->index(['song_id', 'played_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recently_played');
    }
};
