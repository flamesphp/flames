<?php

use Flames\Collection\Arr;

function Arr(mixed $value = null) : Arr
{
    if ($value instanceof Arr) {
        return $value;
    }

    return new \Flames\Collection\Arr($value);
}

///**
// * @internal
// */
//function __shutdown__()
//{
//    Flames\Kernel::shutdown();
//}
//
//register_shutdown_function('__shutdown__');