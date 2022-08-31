<?php

namespace EightAndDouble\LaravelModerator;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelModeratorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * Package Service Provider
         *
         */
        $package
            ->name('laravel-moderator')
            ->hasConfigFile();
    }
}
