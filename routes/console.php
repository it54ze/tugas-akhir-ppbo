<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('vybetify:about', function () {
    $this->info('VYBETIFY — your music, your mood, your vibe.');
})->purpose('Display the VYBETIFY application tagline');
