<?php

namespace Flames\Dump\Parsers;

use Exception;
use Flames\Dump\Inc\DumpHelper;
use Flames\Dump\Inc\DumpParser;
use Flames\Dump\Parsers\DumpParserInterface;

/**
 * @internal
 */
class DumpParsersXml implements DumpParserInterface
{
    public function replacesAllOtherParsers()
    {
        return false;
    }

    public function parse(&$variable, $varData)
    {
        return false; // this is an unsolved problem at humanity level
        if (!DumpHelper::isRichMode()) {
            return false;
        }

        if (is_string($variable) && substr($variable, 0, 5) === '<?xml') {
            try {
                $e = libxml_use_internal_errors(true);
                $xml = simplexml_load_string($variable);
                libxml_use_internal_errors($e);
            } catch (Exception $e) {
                return false;
            }

            if (empty($xml)) {
                return false;
            }
        } else {
            return false;
        }

        //        dd($xml);

        $varData->addTabToView($variable, 'XML', DumpParser::alternativesParse($variable, $xml));
    }
}
