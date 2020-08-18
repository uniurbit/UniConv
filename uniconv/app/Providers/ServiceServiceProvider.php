<?php
namespace App\Providers;
use App\Service\ConvenzioneService;
use App\Service\ApplicationService;
use Illuminate\Support\ServiceProvider;

class ServiceServiceProvider extends ServiceProvider
{
    /**
     * Bind the interface to an implementation service class
     */
    public function register()
    {
        $this->app->bind(
            ApplicationService::class,
            ConvenzioneService::class           
        );
    }
}