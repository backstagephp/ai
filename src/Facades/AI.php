<?php

namespace Backstage\AI\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Backstage\AI\AI
 */
class AI extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Backstage\AI\AI::class;
    }
}
