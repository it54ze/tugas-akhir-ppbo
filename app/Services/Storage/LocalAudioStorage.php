<?php

namespace App\Services\Storage;

use App\Contracts\AudioStorageInterface;

class LocalAudioStorage extends AbstractLocalMediaStorage implements AudioStorageInterface
{
    public function __construct()
    {
        parent::__construct('music');
    }
}
