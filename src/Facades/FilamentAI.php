<?php

namespace Vormkracht10\FilamentAI\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Vormkracht10\FilamentAI\FilamentAI
 */
class FilamentAI extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Vormkracht10\FilamentAI\FilamentAI::class;
    }
}
