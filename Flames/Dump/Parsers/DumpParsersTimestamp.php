<?php

namespace Flames\Dump\Parsers;

use Flames\Dump\Inc\DumpHelper;
use Flames\Dump\Parsers\DumpParserInterface;

/**
 * @internal
 */
class DumpParsersTimestamp implements DumpParserInterface
{
    public function replacesAllOtherParsers()
    {
        return false;
    }

    public function parse(&$variable, $varData)
    {
        if (!$this->_fits($variable)) {
            return false;
        }

        $var = strlen($variable) === 13 ? substr($variable, 0, -3) : $variable;

        // avoid dreaded "Timezone must be set" error
        $varData->addTabToView($variable, 'Timestamp', @date('Y-m-d H:i:s', $var));
    }

    private function _fits($variable)
    {
        if (!DumpHelper::isRichMode()) {
            return false;
        }

        if (!is_string($variable) && !is_int($variable)) {
            return false;
        }

        $len = strlen((int)$variable);

        return
            (
                $len === 9 || $len === 10 // a little naive
                || ($len === 13 && substr($variable, -3) === '000') // also handles javascript micro timestamps
            )
            && ((string)(int)$variable == $variable);
    }
}
