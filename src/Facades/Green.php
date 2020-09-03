<?php

namespace Hedeqiang\Green\Facades;

use Illuminate\Support\Facades\Facade;

class Green extends Facade
{
    /**
     * Return the facade accessor.
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return 'green';
    }

    /**
     * Return the facade accessor.
     * @return \Hedeqiang\Green\Green
     */
    public static function green()
    {
        return app('green');
    }

}
