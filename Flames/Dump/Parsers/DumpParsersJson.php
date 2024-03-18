<?php

namespace Flames\Dump\Parsers;

use Flames\Dump\Inc\DumpHelper;
use Flames\Dump\Parsers\DumpParserInterface;

/**
 * @internal
 */
class DumpParsersJson implements DumpParserInterface
{
    public function replacesAllOtherParsers()
    {
        return false;
    }

    public function parse(&$variable, $varData)
    {
        if (!DumpHelper::isRichMode()
            || !DumpHelper::php53orLater()
            || !is_string($variable)
            || !isset($variable[0])
            || ($variable[0] !== '{' && $variable[0] !== '[')
            || ($json = json_decode($variable, true)) === null
        ) {
            return false;
        }

        $val = (array)$json;

        if (empty($val)) {
            return false;
        }

        $varData->addTabToView($variable, 'Json', $val);
    }
}
