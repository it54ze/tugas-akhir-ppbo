<?php

namespace App\Providers;

use App\Contracts\AudioStorageInterface;
use App\Contracts\CoverStorageInterface;
use App\Services\Storage\LocalAudioStorage;
use App\Models\Playlist;
use App\Services\Storage\LocalCoverStorage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Dependency Inversion: application services depend on contracts.
        $this->app->bind(AudioStorageInterface::class, LocalAudioStorage::class);
        $this->app->bind(CoverStorageInterface::class, LocalCoverStorage::class);
    }

    public function boot(): void
    {
        View::composer('partials.sidebar', function ($view): void {
            $view->with('sidebarPlaylists', Playlist::query()->select(['id', 'name'])->latest()->limit(6)->get());
        });
    }
}
