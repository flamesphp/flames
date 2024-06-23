<?php

use Flames\Dump\Dump;

function dump() : void
{
    ob_start();
    if (!Dump::enabled()) {
        return;
    }

    Dump::$aliases[] = __FUNCTION__;

    $params = func_get_args();
    call_user_func_array(array('Flames\Dump\Dump', 'dump'), $params);

    $buffer = ob_get_contents();
    ob_end_clean();

    $headers = (function_exists('getallheaders') ? getallheaders() : null);;
    if (isset($headers['User-Agent']) === true) {
        if (str_starts_with($headers['User-Agent'], 'PostmanRuntime') === true) {
            $buffer = str_replace('"_sage-parent"', '"_sage-parent _sage-show"', $buffer);
        }
    }

    $buffer = str_replace('>Flames\Collection\Arr</a>', '>Arr</a>', $buffer);
    echo $buffer;
    @flush();
    @ob_flush();
}

function dd()
{
    ob_start();
    if (!Dump::enabled()) {
        return;
    }

    Dump::$aliases[] = __FUNCTION__;

    $params = func_get_args();
    call_user_func_array(array('Flames\Dump\Dump', 'dump'), $params);

    $buffer = ob_get_contents();
    ob_end_clean();

    $headers = (function_exists('getallheaders') ? getallheaders() : null);;
    if (isset($headers['User-Agent']) === true) {
        if (str_starts_with($headers['User-Agent'], 'PostmanRuntime') === true) {
            $buffer = str_replace('"_sage-parent"', '"_sage-parent _sage-show"', $buffer);
        }
    }

    $buffer = str_replace('>Flames\Collection\Arr</a>', '>Arr</a>', $buffer);
    echo $buffer;
    @flush();
    @ob_flush();

    exit;
}