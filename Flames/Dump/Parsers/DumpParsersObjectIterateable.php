<?php

namespace Flames\Dump\Parsers;

use Flames\Dump\Inc\DumpHelper;
use Flames\Dump\Parsers\DumpParserInterface;
use Traversable;

/**
 * @internal
 */
class DumpParsersObjectIterateable implements DumpParserInterface
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
            || !$variable instanceof Traversable
            || stripos($class = get_class($variable), 'zend') !== false // zf2 PDO wrapper does not play nice
            || strpos($class, 'DOMN') !== 0 // DOMNamedNodeMap, DOMNamedNodeMap
        ) {
            return false;
        }

        $arrayCopy = iterator_to_array($variable, true);

        $size = count($arrayCopy);

        $varData->addTabToView($variable, "Iterator contents ({$size})", $arrayCopy);
    }
}
