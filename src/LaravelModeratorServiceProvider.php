<?php

namespace EightAndDouble\LaravelModerator;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use EightAndDouble\LaravelModerator\Commands\LaravelModeratorCommand;

class LaravelModeratorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-moderator')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-moderator_table')
            ->hasCommand(LaravelModeratorCommand::class);
    }
}
