<?php

/*
 * This file is part of the hedeqiang/green.
 *
 * (c) hedeqiang<laravel_code@163.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

return [
    'accessKeyId' => env('GREEN_ACCESS_KEY_ID', ''),
    'accessKeySecret' => env('GREEN_ACCESS_KEY_SECRET', ''),
    'regionId' => env('GREEN_REGION_ID', 'cn-beijing'),
    'timeout' => env('GREEN_TIMEOUT', 6),
    'connectTimeout' => env('GREEN_CONNECT_TIMEOUT', 6),
    'debug' => env('GREEN_DEBUG', false),
];
