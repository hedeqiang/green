<?php

/*
 * This file is part of the hedeqiang/green.
 *
 * (c) hedeqaing<laravel_code@163.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Hedeqiang\Green;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected $defer = true;

    public function boot()
    {
        $this->publishes([
            __DIR__.'/Config/green.php' => config_path('green.php'),
        ]);
    }

    public function register()
    {
        $this->app->singleton(Green::class, function () {
            return new Green(config('green'));
        });

        $this->app->alias(Green::class, 'green');
    }

    public function provides()
    {
        return [Green::class, 'green'];
    }
}
