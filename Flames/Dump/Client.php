<?php

function dump() : void
{
    $debug = debug_backtrace();

    $link = 'phpstorm://open?file={DUMP_LOCAL_PATH}\\';
    $link .= ($debug[1]['class'] . '.php&line=' . $debug[0]['line']);

    $arg = func_get_args()[0];

    if ($arg instanceof \Flames\Element) {
        \Flames\Js::eval('Flames.Internal.dump(
            document.querySelector(\'[\' + Flames.Internal.char + \'uid="' . $arg->uid . '"]\'),
            \' | Called at ' . str_replace('\\', '\\\\', $debug[1]['class'] . ':' . $debug[0]['line'] . ' | ' . $link) . '\'
        );');
        return;
    }

    if (is_object($arg) === true) {
        $arg = (array)$arg;
    }

    $data = base64_encode(json_encode($arg));
    \Flames\Js::eval('Flames.Internal.dump(
        JSON.parse(atob(\'' . $data . '\')),
        \'       | Called at ' . str_replace('\\', '\\\\', $debug[1]['class'] . ':' . $debug[0]['line'] . ' | ' . $link) . '\'
    );');
}

function dd() : void
{
    $debug = debug_backtrace();

    $link = 'phpstorm://open?file={DUMP_LOCAL_PATH}\\';
    $link .= ($debug[1]['class'] . '.php&line=' . $debug[0]['line']);

    $arg = func_get_args()[0];

    if ($arg instanceof \Flames\Element) {
        \Flames\Js::eval('Flames.Internal.dump(
            document.querySelector(\'[\' + Flames.Internal.char + \'uid="' . $arg->uid . '"]\'),
            \' | Called at ' . str_replace('\\', '\\\\', $debug[1]['class'] . ':' . $debug[0]['line'] . ' | ' . $link) . '\'
        );');
        return;
    }

    if (is_object($arg) === true) {
        $arg = (array)$arg;
    }

    $data = base64_encode(json_encode($arg));
    \Flames\Js::eval('Flames.Internal.dump(
        JSON.parse(atob(\'' . $data . '\')),
        \'       | Called at ' . str_replace('\\', '\\\\', $debug[1]['class'] . ':' . $debug[0]['line'] . ' | ' . $link) . '\'
    );');

    exit;
}
