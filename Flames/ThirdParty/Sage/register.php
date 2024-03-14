<?php

use Flames\ThirdParty\Sage\Sage;

function dump() : void
{
    ob_start();
    if (!Sage::enabled()) {
        return;
    }

    Sage::$aliases[] = __FUNCTION__;

    $params = func_get_args();
    call_user_func_array(array('Flames\ThirdParty\Sage\Sage', 'dump'), $params);

    $buffer = ob_get_contents();
    ob_end_clean();

    $headers = null;
    try {
        $headers = getallheaders();
    } catch (\Error $_) {}

    if (isset($headers['User-Agent']) === true) {
        if (str_starts_with($headers['User-Agent'], 'PostmanRuntime') === true) {
            $buffer = str_replace('"_sage-parent"', '"_sage-parent _sage-show"', $buffer);
        }
    }

    $buffer = str_replace('>Flames\Collection\Arr</a>', '>Arr</a>', $buffer);
    echo $buffer;
}