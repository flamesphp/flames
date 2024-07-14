<?php

namespace Flames\Dump\Parsers;

use Flames\Dump\Inc\DumpParser;
use Flames\Dump\Parsers\DumpParserInterface;
use Flames\Dump\Dump;

/**
 * @internal
 */
class DumpParsersBlacklist implements DumpParserInterface
{
    public function replacesAllOtherParsers()
    {
        return true;
    }

    public function parse(&$variable, $varData)
    {
        // allow explicit, first level parameters
        if (DumpParser::$_level === 1) {
            return false;
        }

        if (!is_object($variable)) {
            return false;
        }

        $className = get_class($variable);
        $match = false;
        foreach (Dump::$classNameBlacklist as $item) {
            if (preg_match($item, $className)) {
                $match = true;
                break;
            }
        }

        if (!$match) {
            return false;
        }

        $varData->type = get_class($variable) . ' [skipped]';
    }
}
