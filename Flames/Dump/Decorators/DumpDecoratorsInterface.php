<?php

namespace Flames\Dump\Decorators;

use Flames\Dump\Inc\DumpTraceStep;
use Flames\Dump\Inc\DumpVariableData;

/**
 * @internal
 */
interface DumpDecoratorsInterface
{
    public function decorate(DumpVariableData $varData);

    /** @param DumpTraceStep[] $traceData */
    public function decorateTrace(array $traceData, $pathsOnly = false);

    /**
     * called for each dump, opens the html tag
     *
     * @return string
     */
    public function wrapStart();

    public function wrapEnd($callee, $miniTrace, $prevCaller);

    public function init();
}
