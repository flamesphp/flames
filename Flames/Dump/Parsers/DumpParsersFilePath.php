<?php

namespace Flames\Dump\Parsers;

use Flames\Dump\Inc\DumpHelper;
use Flames\Dump\Parsers\DumpParserInterface;
use Flames\Dump\Parsers\DumpParsersSplFileInfo;
use SplFileInfo;

/**
 * @internal
 */
class DumpParsersFilePath extends DumpParsersSplFileInfo implements DumpParserInterface
{
    public function replacesAllOtherParsers()
    {
        return false;
    }

    public function parse(&$variable, $varData)
    {
        if (!DumpHelper::php53orLater()
            || !is_string($variable)
            || ($strlen = strlen($variable)) > 2048
            || $strlen < 3
            || !preg_match('#[\\\\/]#', $variable)
            || preg_match('/[?<>"*|]/', $variable)
            || !@is_readable($variable) // PHP and its random warnings
        ) {
            return false;
        }

        return $this->run($variable, $varData, new SplFileInfo($variable));
    }
}
