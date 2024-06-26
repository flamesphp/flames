<?php

namespace Flames\Dump\Parsers;

use Closure;
use Flames\Dump\Inc\DumpHelper;
use Flames\Dump\Inc\DumpParser;
use Flames\Dump\Parsers\DumpParserInterface;
use ReflectionFunction;

/**
 * @internal
 * @noinspection AutoloadingIssuesInspection
 */
class DumpParsersClosure implements DumpParserInterface
{
    public function replacesAllOtherParsers()
    {
        return true;
    }

    public function parse(&$variable, $varData)
    {
        if (!$variable instanceof Closure) {
            return false;
        }

        $varData->type = 'Closure';
        $reflection = new ReflectionFunction($variable);

        $parameters = array();
        foreach ($reflection->getParameters() as $parameter) {
            $parameters = $parameter->name;
        }
        if (!empty($parameters)) {
            $varData->addTabToView($variable, 'Closure Parameters', $parameters);
        }

        $uses = array();
        if ($val = $reflection->getStaticVariables()) {
            $uses = $val;
        }
        if (method_exists($reflection, 'getClousureThis') && $val = $reflection->getClosureThis()) {
            $uses[] = DumpParser::process($val, 'Closure $this');
        }
        if (!empty($uses)) {
            $varData->addTabToView($variable, 'Closure Parameters', $uses);
        }

        if ($reflection->getFileName()) {
            $varData->value = DumpHelper::ideLink($reflection->getFileName(), $reflection->getStartLine());
        }
    }
}
