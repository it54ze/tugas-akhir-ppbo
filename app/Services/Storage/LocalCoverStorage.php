<?php

namespace App\Services\Storage;

use App\Contracts\CoverStorageInterface;

class LocalCoverStorage extends AbstractLocalMediaStorage implements CoverStorageInterface
{
    public function __construct()
    {
        parent::__construct('covers');
    }
}
