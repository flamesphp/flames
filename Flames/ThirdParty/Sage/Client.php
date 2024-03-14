<?php

function dump() : void
{
    $debug = debug_backtrace()[1];

    $link = 'phpstorm://open?file=C:\Dev\repository\kazzcorp\flames\\';
    $link .= ($debug['class'] . '.php&line=' . $debug['line']);

    $arg = func_get_args()[0];

    if (is_object($arg) === true) {
        $arg = (array)$arg;
    }

    $data = base64_encode(json_encode($arg));

    \Flames\JS::eval('console.log(\'' . str_replace('\\', '\\\\', $debug['class'] . ':' . $debug['line'] . ' ' . $link) . '\');');
    \Flames\JS::eval('console.log(JSON.parse(atob(\'' . $data . '\')));');
}