<?php

namespace EightAndDouble\LaravelModerator\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \EightAndDouble\LaravelModerator\LaravelModerator
 */
class LaravelModerator extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \EightAndDouble\LaravelModerator\LaravelModerator::class;
    }
}
