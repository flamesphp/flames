<?php

namespace Flames\ThirdParty\Sage\Parsers;

use Flames\ThirdParty\Sage\inc\SageHelper;
use Flames\ThirdParty\Sage\parsers\SageParserInterface;

/**
 * @internal
 */
class SageParsersJson implements SageParserInterface
{
    public function replacesAllOtherParsers()
    {
        return false;
    }

    public function parse(&$variable, $varData)
    {
        if (!SageHelper::isRichMode()
            || !SageHelper::php53orLater()
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
