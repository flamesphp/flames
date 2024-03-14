<?php

namespace Flames\ThirdParty\Sage\Parsers;
/**
 * @internal
 */
interface SageParserInterface
{
    public function replacesAllOtherParsers();

    public function parse(&$variable, $varData);
}
