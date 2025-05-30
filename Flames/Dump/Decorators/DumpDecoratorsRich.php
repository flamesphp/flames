<?php

namespace Flames\Dump\Decorators;

use Flames\Dump\Decorators\DumpDecoratorsInterface;
use Flames\Dump\Inc\DumpHelper;
use Flames\Dump\Inc\DumpParser;
use Flames\Dump\Inc\DumpTraceStep;
use Flames\Dump\Inc\DumpVariableData;
use Flames\Dump\Dump;

/**
 * @internal
 */
class DumpDecoratorsRich implements DumpDecoratorsInterface
{
    protected static $needsAssets = true;

    public function areAssetsNeeded()
    {
        return self::$needsAssets;
    }

    public function setAssetsNeeded($added)
    {
        self::$needsAssets = $added;
    }

    public function decorate(DumpVariableData $varData)
    {
        $output = '<dl>';

        $allRepresentations = $varData->getAllRepresentations();
        $extendedPresent = !empty($allRepresentations);

        if ($extendedPresent) {
            $class = '_sage-parent';
            if (Dump::$expandedByDefault) {
                $class .= ' _sage-show';
            }
            $output .= '<dt class="' . $class . '">';
        } else {
            $output .= '<dt>';
        }

        if ($extendedPresent) {
            $output .= '<span class="_sage-popup-trigger">&rarr;</span><nav></nav>';
        }

        $output .= $this->_drawHeader($varData) . $varData->value . "</dt>";

        if ($extendedPresent) {
            $output .= '<dd>';
        }

        if (count($allRepresentations) === 1 && !empty($varData->extendedValue)) {
            $extendedValue = reset($allRepresentations);
            $output .= $this->decorateAlternativeView($extendedValue);
        } elseif ($extendedPresent) {
            $output .= "<ul class=\"_sage-tabs\">";

            $isFirst = true;
            foreach ($allRepresentations as $tabName => $_) {
                $active = $isFirst ? ' class="_sage-active-tab"' : '';
                $isFirst = false;
                $output .= "<li{$active}>" . DumpHelper::esc($tabName) . '</li>';
            }

            $output .= '</ul><ul>';

            foreach ($allRepresentations as $alternative) {
                $output .= '<li>';
                $output .= $this->decorateAlternativeView($alternative);
                $output .= '</li>';
            }

            $output .= '</ul>';
        }
        if ($extendedPresent) {
            $output .= '</dd>';
        }

        $output .= "</dl>\n";

        return $output;
    }

    /** @param DumpTraceStep[] $traceData */
    public function decorateTrace(array $traceData, $pathsOnly = false)
    {
        $output = '<dl class="_sage-trace">';

        $blacklistedStepsInARow = 0;
        foreach ($traceData as $i => $step) {
            if ($step->isBlackListed) {
                $blacklistedStepsInARow++;
                continue;
            }

            if ($blacklistedStepsInARow) {
                if ($blacklistedStepsInARow <= 5) {
                    for ($j = $blacklistedStepsInARow; $j > 0; $j--) {
                        $output .= $this->drawTraceStep($i - $j, $traceData[$i - $j], $pathsOnly);
                    }
                } else {
                    $output .= "<dt><b></b>[{$blacklistedStepsInARow} steps skipped]</dt>";
                }

                $blacklistedStepsInARow = 0;
            }

            $output .= $this->drawTraceStep($i, $step, $pathsOnly);
        }

        if ($blacklistedStepsInARow > 1) {
            $output .= "<dt><b></b>[{$blacklistedStepsInARow} steps skipped]</dt>";
        }

        $output .= '</dl>';

        return $output;
    }

    private function drawTraceStep($i, $step, $pathsOnly)
    {
        $isChildless = !$step->sourceSnippet && !$step->arguments && !$step->object;

        $class = '';

        if ($step->isBlackListed) {
            $class .= ' _sage-blacklisted';
        } elseif ($isChildless) {
            $class .= ' _sage-childless';
        } else {
            $class .= '_sage-parent';

            if (Dump::$expandedByDefault) {
                $class .= ' _sage-show';
            }
        }

        $output = '<dt class="' . $class . '">';
        $output .= '<b>' . ($i + 1) . '</b>';
        if (!$isChildless) {
            $output .= '<nav></nav>';
        }
        $output .= '<var>' . $step->fileLine . '</var> ';
        $output .= $step->functionName;
        $output .= '</dt>';

        if ($isChildless) {
            return $output;
        }

        $output .= '<dd><ul class="_sage-tabs">';
        $firstTabClass = ' class="_sage-active-tab"';

        if ($step->sourceSnippet) {
            $output .= "<li{$firstTabClass}>Source</li>";
            $firstTabClass = '';
        }

        if (!$pathsOnly && $step->arguments) {
            $output .= "<li{$firstTabClass}>Arguments</li>";
            $firstTabClass = '';
        }

        if (!$pathsOnly && $step->object) {
            $output .= "<li{$firstTabClass}>Callee object [{$step->object->type}]</li>";
        }

        $output .= '</ul><ul>';

        if ($step->sourceSnippet) {
            $output .= "<li><pre class=\"_sage-source\">{$step->sourceSnippet}</pre></li>";
        }

        if (!$pathsOnly && $step->arguments) {
            $output .= '<li>';
            foreach ($step->arguments as $argument) {
                $output .= $this->decorate($argument);
            }
            $output .= '</li>';
        }

        if (!$pathsOnly && $step->object) {
            $output .= '<li>' . $this->decorate($step->object) . '</li>';
        }

        $output .= '</ul></dd>';

        return $output;
    }

    /**
     * called for each dump, opens the html tag
     *
     * @return string
     */
    public function wrapStart()
    {
        return "<div class=\"_sage\">";
    }

    /**
     * closes Dump::_wrapStart() started html tags and displays callee information
     *
     * @param array $callee caller information taken from debug backtrace
     * @param array $miniTrace full path to Dump call
     * @param array $prevCaller previous caller information taken from debug backtrace
     *
     * @return string
     */
    public function wrapEnd($callee, $miniTrace, $prevCaller)
    {
        if (!Dump::$displayCalledFrom) {
            return '</div>';
        }

        $callingFunction = '';
        $calleeInfo = '';
        $traceDisplay = '';
        if (isset($prevCaller['class'])) {
            $callingFunction = $prevCaller['class'];
        }
        if (isset($prevCaller['type'])) {
            $callingFunction .= $prevCaller['type'];
        }
        if (isset($prevCaller['function'])
            && !in_array($prevCaller['function'], array('include', 'include_once', 'require', 'require_once'))
        ) {
            $callingFunction .= $prevCaller['function'] . '()';
        }
        $callingFunction and $callingFunction = " [{$callingFunction}]";

        if (isset($callee['file'])) {
            $calleeInfo .= 'Called from ' . DumpHelper::ideLink($callee['file'], $callee['line']);
        }

        if (!empty($miniTrace)) {
            $traceDisplay = '<ol>';
            foreach ($miniTrace as $step) {
                $traceDisplay .= '<li>' . DumpHelper::ideLink($step['file'], $step['line']); // closing tag not required
                if (isset($step['function'])
                    && !in_array($step['function'], array('include', 'include_once', 'require', 'require_once'))
                ) {
                    $classString = ' [';
                    if (isset($step['class'])) {
                        $classString .= $step['class'];
                    }
                    if (isset($step['type'])) {
                        $classString .= $step['type'];
                    }
                    $classString .= $step['function'] . '()]';
                    $traceDisplay .= $classString;
                }
            }
            $traceDisplay .= '</ol>';

            $calleeInfo = '<nav></nav>' . $calleeInfo;
        }

        $callingFunction .= ' @ ' . date('Y-m-d H:i:s');

        return '<footer>'
            . '<span class="_sage-popup-trigger" title="Open in new window">&rarr;</span> '
            . "{$calleeInfo}{$callingFunction}{$traceDisplay}"
            . '</footer></div>';
    }

    private function _drawHeader(DumpVariableData $varData)
    {
        $output = '';
        if ($varData->access !== null) {
            $output .= "<var>{$varData->access}</var> ";
        }

        if ($varData->name !== null && $varData->name !== '') {
            $output .= '<dfn>' . DumpHelper::esc($varData->name) . '</dfn> ';
        }

        if ($varData->operator !== null) {
            $output .= $varData->operator . ' ';
        }

        if ($varData->type !== null) {
            // tyoe output is unescaped as it is set internally and contains links to user class
            $output .= "<var>{$varData->type}</var> ";
        }

        if ($varData->size !== null) {
            $output .= '(' . $varData->size . ') ';
        }

        return $output;
    }

    /**
     * produces css and js required for display. May be called multiple times, will only produce output once per
     * pageload or until `-` or `@` modifier is used
     *
     * @return string
     */
    public function init()
    {
        $baseDir = DUMP_DIR . 'resources/compiled/';

        if (!is_readable($cssFile = $baseDir . Dump::$theme . '.css')) {
            $cssFile = $baseDir . 'original.css';
        }

        $defaultCss = file_get_contents($cssFile);
        $defaultCss = str_replace('body{background:#073642;color:#fff}', '', $defaultCss);

        $mountHtml =
            '<script class="_sage-js">' . file_get_contents($baseDir . 'sage.js') . '</script>'
            . '<style class="_sage-css">' . $defaultCss . "</style>";

        $cssFileDark = (dirname($cssFile) . '/dark.css');
        $mountHtml .= '<style class="_sage-css">' . file_get_contents($cssFileDark) . "</style>";
        $mountHtml .= "\n";

        return $mountHtml;
    }

    private function decorateAlternativeView($alternative)
    {
        if (empty($alternative)) {
            return '';
        }

        $output = '';
        if (is_array($alternative)) {
            // we either get a prepared array of DumpVariableData or a raw array of anything
            $parse = reset($alternative) instanceof DumpVariableData
                ? $alternative
                : DumpParser::process($alternative)->extendedValue; // convert into DumpVariableData[]

            foreach ($parse as $v) {
                $output .= $this->decorate($v);
            }
        } elseif (is_string($alternative)) {
            // the browser does not render leading new line in <pre>
            if ($alternative[0] === "\n" || $alternative[0] === "\r") {
                $alternative = "\n" . $alternative;
            }
            $output .= "<pre>{$alternative}</pre>";
        }

        return $output;
    }

}
