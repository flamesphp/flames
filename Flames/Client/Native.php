<?php

namespace Flames\Client;

use Flames\Kernel\Client\Dispatch\Native as DispatchNative;

class Native
{
    protected static object|null $info = null;

    public static function close(): void
    { DispatchNative::add('exit'); }
}