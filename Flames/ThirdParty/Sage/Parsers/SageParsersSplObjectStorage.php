<?php

namespace Flames\ThirdParty\Sage\Parsers;

use Flames\ThirdParty\Sage\inc\SageHelper;
use Flames\ThirdParty\Sage\parsers\SageParserInterface;
use SplObjectStorage;

/**
 * @internal
 */
class SageParsersSplObjectStorage implements SageParserInterface
{
    public function replacesAllOtherParsers()
    {
        return false;
    }

    public function parse(&$variable, $varData)
    {
        if (!SageHelper::isRichMode() || !is_object($variable) || !$variable instanceof SplObjectStorage) {
            return false;
        }

        $count = $variable->count();
        if ($count === 0) {
            return false;
        }

        $variable->rewind();
        $arrayCopy = array();
        while ($variable->valid()) {
            $arrayCopy[] = $variable->current();
            $variable->next();
        }

        $varData->addTabToView($variable, "Storage contents ({$count})", $arrayCopy);
    }
}
