<?php

return [
    'uploads' => [
        'audio_max_kb' => (int) env('VYBETIFY_AUDIO_MAX_KB', 65536),
        'cover_max_kb' => (int) env('VYBETIFY_COVER_MAX_KB', 8192),
    ],
];
