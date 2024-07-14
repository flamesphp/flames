<?php

namespace Flames\Dump\Parsers;
/**
 * @internal
 */
interface DumpParserInterface
{
    public function replacesAllOtherParsers();

    public function parse(&$variable, $varData);
}
