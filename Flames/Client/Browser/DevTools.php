<?php

namespace Flames\Client\Browser;

use Flames\Kernel\Client\Dispatch\Native;

class DevTools
{
    public static function open(): void
    { Native::add('devtools-open'); }

    public static function close(): void
    { Native::add('devtools-close'); }
}