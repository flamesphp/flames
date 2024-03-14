<?php
/**
 * Whoops - php errors for cool kids
 * @author Filipe Dobreira <http://github.com/filp>
 */

namespace Flames\ThirdParty\Whoops\Util;

/**
 * @internal
 */
class HtmlDumperOutput
{
    private $output;

    public function __invoke($line, $depth)
    {
        // A negative depth means "end of dump"
        if ($depth >= 0) {
            // Adds a two spaces indentation to the line
            $this->output .= str_repeat('  ', $depth) . $line . "\n";
        }
    }

    public function getOutput()
    {
        return $this->output;
    }

    public function clear()
    {
        $this->output = null;
    }
}
