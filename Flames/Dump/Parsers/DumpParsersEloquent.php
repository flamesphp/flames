<?php

namespace Flames\Dump\Parsers;

use Flames\Dump\Inc\DumpHelper;
use Flames\Dump\Inc\DumpParser;
use Flames\Dump\Parsers\DumpParserInterface;
use ReflectionObject;

/**
 * @internal
 */
class DumpParsersEloquent implements DumpParserInterface
{
    public function replacesAllOtherParsers()
    {
        return true;
    }

    public function parse(&$variable, $varData)
    {
        if (!DumpHelper::php53orLater() || !is_a($variable, '\Illuminate\Database\Eloquent\Model')) {
            return false;
        }

        $reflection = new ReflectionObject($variable);

        $attrReflecion = $reflection->getProperty('attributes');
        $attrReflecion->setAccessible(true);
        $attributes = $attrReflecion->getValue($variable);

        $reference = '`' . $variable->getConnection()->getDatabaseName() . '`.`' . $variable->getTable() . '`';

        $varData->size = count($attributes);
        if (DumpHelper::isRichMode()) {
            $varData->type = $reflection->getName();
            $varData->addTabToView($variable, 'data from ' . $reference, $attributes);
        } else {
            $varData->type = $reflection->getName() . '; ' . $reference . ' row data:';
            $varData->extendedValue = DumpParser::alternativesParse($variable, $attributes);
        }
    }
}
