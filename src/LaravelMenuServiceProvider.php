<?php

namespace Stianscholtz\LaravelMenu;

use Illuminate\Support\ServiceProvider;
use Stianscholtz\LaravelMenu\Commands\MenuMakeCommand;

class LaravelMenuServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if($this->app->runningInConsole()) {
            $this->commands([
                MenuMakeCommand::class
            ]);
        }
    }
}