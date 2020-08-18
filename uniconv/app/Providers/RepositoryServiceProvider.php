<?php
namespace App\Providers;
use App\Repositories\ConvenzioneRepository;
use App\Repositories\RepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Bind the interface to an implementation repository class
     */
    public function register()
    {
        $this->app->bind(
            RepositoryInterface::class,
            ConvenzioneRepository::class           
        );
    }
}