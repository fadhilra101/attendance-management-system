<?php

namespace App\Providers;

use App\Services\CsvExporter;
use Illuminate\Support\ServiceProvider;

class ServicesServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('App\Services\CsvExporter', function ($app) {
            return new CsvExporter();
        });
    }

    public function boot()
    {
        //
    }
}
