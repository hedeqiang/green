<?php

/*
 * This file is part of the hedeqiang/green.
 *
 * (c) hedeqiang<laravel_code@163.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

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
     *
     * @return \Hedeqiang\Green\Green
     */
    public static function green()
    {
        return app('green');
    }
}
