<?php

namespace Flames\Dump\Parsers;

use Flames\Dump\Inc\DumpHelper;
use Flames\Dump\Parsers\DumpParserInterface;
use Flames\Model;
use Traversable;

/**
 * @internal
 */
class DumpParsersFlamesModel implements DumpParserInterface
{
    public function replacesAllOtherParsers()
    {
        return false;
    }

    public function parse(&$variable, $varData)
    {
        if (!DumpHelper::isRichMode()
            || !DumpHelper::php53orLater()
            || !is_object($variable)
            || !$variable instanceof Model
        ) {
            return false;
        }

        $arrayCopy = $variable->toArray();
        $size = count($arrayCopy);

        $modelDetails = [
            'database' => $variable::getDatabase(),
            'table'    => $variable::getTable()
        ];

        $varData->addTabToView($variable, "Model contents ({$size})", $arrayCopy);
        $varData->addTabToView($variable, "Model details", $modelDetails);
    }
}
