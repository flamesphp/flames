<?php

// Sage fork: https://github.com/php-sage/sage

namespace Flames\Dump;

use Flames\Dump\Decorators\DumpDecoratorsPlain;
use Flames\Dump\Decorators\DumpDecoratorsRich;
use Flames\Dump\Inc\DumpHelper;
use Flames\Dump\Inc\DumpParser;
use Flames\Dump\Inc\DumpTraceStep;
use Flames\Dump\Exception;
use Flames\Dump\Throwable;

if (defined('DUMP_DIR')) {
    return;
}
define('DUMP_DIR', dirname(__FILE__) . '/');

//require DUMP_DIR . 'inc/DumpVariableData.php';
//require DUMP_DIR . 'inc/DumpTraceStep.php';
//require DUMP_DIR . 'inc/DumpParser.php';
//require DUMP_DIR . 'inc/DumpHelper.php';
////require DUMP_DIR . 'inc/shorthands.inc.php';
//require DUMP_DIR . 'decorators/DumpDecoratorsInterface.php';
//require DUMP_DIR . 'decorators/DumpDecoratorsRich.php';
//require DUMP_DIR . 'decorators/DumpDecoratorsPlain.php';
//require DUMP_DIR . 'parsers/DumpParserInterface.php';

/**
 * @internal
 */
class Dump
{
    private static $_initialized = false;
    private static $_enabledMode = true;
    private static $_openedOutput;

    /*
     *     ██████╗ ██████╗ ███╗   ██╗███████╗██╗ ██████╗ ██╗   ██╗██████╗  █████╗ ████████╗██╗ ██████╗ ███╗   ██╗
     *    ██╔════╝██╔═══██╗████╗  ██║██╔════╝██║██╔════╝ ██║   ██║██╔══██╗██╔══██╗╚══██╔══╝██║██╔═══██╗████╗  ██║
     *    ██║     ██║   ██║██╔██╗ ██║█████╗  ██║██║  ███╗██║   ██║██████╔╝███████║   ██║   ██║██║   ██║██╔██╗ ██║
     *    ██║     ██║   ██║██║╚██╗██║██╔══╝  ██║██║   ██║██║   ██║██╔══██╗██╔══██║   ██║   ██║██║   ██║██║╚██╗██║
     *    ╚██████╗╚██████╔╝██║ ╚████║██║     ██║╚██████╔╝╚██████╔╝██║  ██║██║  ██║   ██║   ██║╚██████╔╝██║ ╚████║
     *     ╚═════╝ ╚═════╝ ╚═╝  ╚═══╝╚═╝     ╚═╝ ╚═════╝  ╚═════╝ ╚═╝  ╚═╝╚═╝  ╚═╝   ╚═╝   ╚═╝ ╚═════╝ ╚═╝  ╚═══╝
     *
     * ASCII ART: patorjk.com/software/taag/#p=display&h=1&v=2&c=c&f=ANSI Shadow&t=
     */

    /**
     * @var string makes visible source file paths clickable to open your editor.
     *
     * Pre-defined values:
     *   'sublime'                => 'subl://open?url=file://%file&line=%line',
     *   'textmate'               => 'txmt://open?url=file://%file&line=%line',
     *   'emacs'                  => 'emacs://open?url=file://%file&line=%line',
     *   'macvim'                 => 'mvim://open/?url=file://%file&line=%line',
     *   'phpstorm'               => 'phpstorm://open?file=%file&line=%line',
     *   'phpstorm-remote'        => 'http://localhost:63342/api/file/%file:%line',
     *   'idea'                   => 'idea://open?file=%file&line=%line',
     *   'vscode'                 => 'vscode://file/%file:%line',
     *   'vscode-insiders'        => 'vscode-insiders://file/%file:%line',
     *   'vscode-remote'          => 'vscode://vscode-remote/%file:%line',
     *   'vscode-insiders-remote' => 'vscode-insiders://vscode-remote/%file:%line',
     *   'vscodium'               => 'vscodium://file/%file:%line',
     *   'atom'                   => 'atom://core/open/file?filename=%file&line=%line',
     *   'nova'                   => 'nova://core/open/file?filename=%file&line=%line',
     *   'netbeans'               => 'netbeans://open/?f=%file:%line',
     *   'xdebug'                 => 'xdebug://%file@%line'
     *
     * Or pass a custom string where %fileileileileileile should be replaced with full file path, %line with line number
     * to create a custom link. Set to null to disable linking.
     *
     * Example:
     *             // works with for PHPStorm and IDE Remote Control Plugin
     *             Dump::$editor = 'phpstorm-remote';
     * Example:
     *             // same result as above, but explicitly defined
     *             Dump::$editor = 'http://localhost:63342/api/file/f:%line';
     *
     * Default:
     *             ini_get('xdebug.file_link_format') ?: 'phpstorm-remote'
     *
     */
    public static $editor;

    /**
     * @var string the full path (not URL) to your project folder on your remote dev server, be this Homestead, Docker,
     *             or in the cloud.
     *
     * Default:
     *             null
     */
    public static $fileLinkServerPath;

    /**
     * @var string the full path (not URL) to your project on your local machine, the way your IDE or editor accesses
     *             the files.
     *
     * Default:
     *             null
     */
    public static $fileLinkLocalPath;

    /**
     * @var bool whether to display where Dump was called from
     *
     * Default:
     *           true
     */
    public static $displayCalledFrom;

    /**
     * @var int max array/object levels to go deep, set to zero/false to disable
     *
     * Default:
     *          7
     */
    public static $maxLevels = 20;

    /**
     * @var string theme for rich view
     *
     * Example:
     *             Dump::$theme = Dump::THEME_ORIGINAL;
     *             Dump::$theme = Dump::THEME_LIGHT;
     *             Dump::$theme = Dump::THEME_SOLARIZED;
     *             Dump::$theme = Dump::THEME_SOLARIZED_DARK;
     *
     * Default:
     *             Dump::THEME_ORIGINAL
     */
    public static $theme;

    /**
     * @var bool draw rich output already expanded without having to click
     *
     * Default:
     *           false
     */
    public static $expandedByDefault;

    /**
     * @var bool enable detection when running in command line and adjust output format accordingly.
     *
     * Default:
     *           true
     */
    public static $cliDetection;

    /**
     * @var bool in addition to above setting, enable detection when Dump is run in *UNIX* command line.
     * Attempts to add coloring, but if seen as plain text, the color information is visible as gibberish
     *
     * Default:
     *           true
     */
    public static $cliColors;

    /**
     * @var array possible alternative char encodings in order of probability,
     *
     * Default:
     *           array(
     *               'UTF-8',
     *               'Windows-1252', // Western; includes iso-8859-1, replace this with windows-1251 if you use Russian
     *               'euc-jp',       // Japanese
     *           );
     */
    public static $charEncodings;

    /**
     * @var bool|string Dump returns output instead of echo.
     *
     * If true, the return has scripts+css always included, if set to a string, only first time per "group".
     *
     * Default:
     *           false
     */
    public static $returnOutput;

    /**
     * @var string Write output to this file instead of echoing it. If it ends in `.html` forces output in html mode.
     *
     * Default:
     *           false
     */
    public static $outputFile;

    /**
     * @var array Add new custom Dump wrapper names. Needed for nice backtraces, variable name detection and modifiers.
     *
     *            [!] Use notation `Class::method` for methods.
     *
     * Example:
     *            function doom_dump($args)
     *            {
     *                echo "DOOOM!";
     *                d(...func_get_args());
     *            }
     *            Dump::$aliases = 'doom_dump';
     *
     * Default:
     *            array()
     */
    public static $aliases = array();

    /*
     *    ██╗    ██╗██╗██████╗
     *    ██║    ██║██║██╔══██╗
     *    ██║ █╗ ██║██║██████╔╝
     *    ██║███╗██║██║██╔═══╝
     *    ╚███╔███╔╝██║██║
     *     ╚══╝╚══╝ ╚═╝╚═╝
     */
    /**
     * @var string[] keys don't matter, but you can use them to unset a particular entry.
     */
    public static $traceBlacklist = array(
        'vendor' => '#\/vendor\/#',
        'middleware' => '#\/Middleware\/#'
    );

    public static $classNameBlacklist = array(
        'illuminate' => '/^Illuminate(?!.*(?:Exception|Collection))/'
        // 'symfony'    => '/^Symfony/'
    );

    public static $arrayKeysBlacklist = array();

    public static $minimumTraceStepsToShowFull = 1;

    /** @var class-string<DumpParser>[] */
    public static $enabledParsers = array(
        'DumpParsersFlamesModel' => true,
        'DumpParsersSmarty' => true,
        'DumpParsersSplFileInfo' => true,
        'DumpParsersClosure' => true,
        'DumpParsersEloquent' => true,
        'DumpParsersDateTime' => true,
        'DumpParsersSplObjectStorage' => true,
        'DumpParsersTimestamp' => true,
        'DumpParsersFilePath' => true,
        // above this line are only those parsers that $replacesAllOtherParsers

        // now we run the blacklist
        'DumpParsersBlacklist' => true,

        // all the rest
        'DumpParsersXml' => true,
        'DumpParsersObjectIterateable' => true,
        'DumpParsersClassStatics' => true,
        'DumpParsersColor' => true,
        'DumpParsersJson' => true,
        'DumpParsersClassName' => true,
        'DumpParsersMicrotime' => true
    );

    public static function saveState($state = array())
    {
        $rich = new DumpDecoratorsRich();
        $plain = new DumpDecoratorsPlain();

        if (func_num_args()) {
            self::$_enabledMode = $state['enabled'];
            self::$editor = $state['editor'];
            self::$fileLinkServerPath = $state['fileLinkServerPath'];
            self::$fileLinkLocalPath = $state['fileLinkLocalPath'];
            self::$displayCalledFrom = $state['displayCalledFrom'];
            self::$maxLevels = $state['maxLevels'];
            self::$theme = $state['theme'];
            self::$expandedByDefault = $state['expandedByDefault'];
            self::$cliDetection = $state['cliDetection'];
            self::$cliColors = $state['cliColors'];
            self::$charEncodings = $state['charEncodings'];
            self::$returnOutput = $state['returnOutput'];
            self::$outputFile = $state['outputFile'];
            self::$aliases = $state['aliases'];
            self::$traceBlacklist = $state['traceBlacklist'];
            self::$classNameBlacklist = $state['classNameBlacklist'];
            self::$enabledParsers = $state['enabledParsers'];

            $rich->setAssetsNeeded($state['DumpDecoratorsRich::firstRun']);
            $plain->setAssetsNeeded($state['DumpDecoratorsPlain::firstRun']);

            return;
        }

        return array(
            'enabled' => self::$_enabledMode,
            'editor' => self::$editor,
            'fileLinkServerPath' => self::$fileLinkServerPath,
            'fileLinkLocalPath' => self::$fileLinkLocalPath,
            'displayCalledFrom' => self::$displayCalledFrom,
            'maxLevels' => self::$maxLevels,
            'theme' => self::$theme,
            'expandedByDefault' => self::$expandedByDefault,
            'cliDetection' => self::$cliDetection,
            'cliColors' => self::$cliColors,
            'charEncodings' => self::$charEncodings,
            'returnOutput' => self::$returnOutput,
            'outputFile' => self::$outputFile,
            'aliases' => self::$aliases,
            'traceBlacklist' => self::$traceBlacklist,
            'classNameBlacklist' => self::$classNameBlacklist,
            'enabledParsers' => self::$enabledParsers,
            'DumpDecoratorsRich::firstRun' => $rich->areAssetsNeeded(),
            'DumpDecoratorsPlain::firstRun' => $plain->areAssetsNeeded()
        );
    }

    /**
     * @var bool there are multiple ways to direct sage to display "simpler" view than current mode (e.g. Rich -> PLain)
     * todo must be private
     */
    public static $simplifyDisplay = false;

    /*
     *     ██████╗ ██████╗ ███╗   ██╗███████╗████████╗ █████╗ ███╗   ██╗████████╗███████╗
     *    ██╔════╝██╔═══██╗████╗  ██║██╔════╝╚══██╔══╝██╔══██╗████╗  ██║╚══██╔══╝██╔════╝
     *    ██║     ██║   ██║██╔██╗ ██║███████╗   ██║   ███████║██╔██╗ ██║   ██║   ███████╗
     *    ██║     ██║   ██║██║╚██╗██║╚════██║   ██║   ██╔══██║██║╚██╗██║   ██║   ╚════██║
     *    ╚██████╗╚██████╔╝██║ ╚████║███████║   ██║   ██║  ██║██║ ╚████║   ██║   ███████║
     *     ╚═════╝ ╚═════╝ ╚═╝  ╚═══╝╚══════╝   ╚═╝   ╚═╝  ╚═╝╚═╝  ╚═══╝   ╚═╝   ╚══════╝
     *
     */

    const MODE_RICH = 'r';
    const MODE_TEXT_ONLY = 'w';
    const MODE_CLI = 'c';
    const MODE_PLAIN = 'p';

    const THEME_ORIGINAL = 'original';
    const THEME_LIGHT = 'aante-light';
    const THEME_ORIGINAL_LIGHT = 'original-light';
    const THEME_SOLARIZED_DARK = 'solarized-dark';
    const THEME_SOLARIZED = 'solarized';

    /*
     *    ███████╗███╗   ██╗ █████╗ ██████╗ ██╗     ███████╗██████╗
     *    ██╔════╝████╗  ██║██╔══██╗██╔══██╗██║     ██╔════╝██╔══██╗
     *    █████╗  ██╔██╗ ██║███████║██████╔╝██║     █████╗  ██║  ██║
     *    ██╔══╝  ██║╚██╗██║██╔══██║██╔══██╗██║     ██╔══╝  ██║  ██║
     *    ███████╗██║ ╚████║██║  ██║██████╔╝███████╗███████╗██████╔╝
     *    ╚══════╝╚═╝  ╚═══╝╚═╝  ╚═╝╚═════╝ ╚══════╝╚══════╝╚═════╝
     */

    /**
     * Enables or disables Dump, and forces display mode. Also returns currently active mode.
     *
     * @param mixed $forceMode
     *                        null or void - return current mode
     *                        false        - disable Dump
     *                        true         - enable Dump and allow it to auto-detect the best formatting
     *                        Dump::MODE_* - enable and force selected mode:
     *                        -      Dump::MODE_RICH         Rich Text HTML
     *                        -      Dump::MODE_PLAIN        Plain-view, HTML formatted output
     *                        -      Dump::MODE_CLI          Console-formatted colored output
     *                        -      Dump::MODE_TEXT_ONLY    Non-escaped plain text mode
     *
     * @return mixed            previously set value
     */
    public static function enabled($forceMode = null)
    {
        // act both as a setter...
        if (isset($forceMode)) {
            $before = self::$_enabledMode;
            self::$_enabledMode = $forceMode;

            return $before;
        }

        // ...and a getter
        return self::$_enabledMode;
    }

    /*
     *    ████████╗██████╗  █████╗  ██████╗███████╗    ██╗██████╗ ██╗   ██╗███╗   ███╗██████╗
     *    ╚══██╔══╝██╔══██╗██╔══██╗██╔════╝██╔════╝   ██╔╝██╔══██╗██║   ██║████╗ ████║██╔══██╗
     *       ██║   ██████╔╝███████║██║     █████╗    ██╔╝ ██║  ██║██║   ██║██╔████╔██║██████╔╝
     *       ██║   ██╔══██╗██╔══██║██║     ██╔══╝   ██╔╝  ██║  ██║██║   ██║██║╚██╔╝██║██╔═══╝
     *       ██║   ██║  ██║██║  ██║╚██████╗███████╗██╔╝   ██████╔╝╚██████╔╝██║ ╚═╝ ██║██║
     *       ╚═╝   ╚═╝  ╚═╝╚═╝  ╚═╝ ╚═════╝╚══════╝╚═╝    ╚═════╝  ╚═════╝ ╚═╝     ╚═╝╚═╝
     *
     */

    /**
     * Prints a debug backtrace, same as `sage(1)`.
     *
     * Skip trace arguments and only see the paths - `sage(2)`
     *
     * @param array $trace [OPTIONAL] you can pass your own trace, otherwise, `debug_backtrace` will be called
     *
     * @return mixed
     */
    public static function trace($trace = null)
    {
        if ($trace === null) {
            $trace = DumpHelper::php53orLater() ? debug_backtrace(true) : debug_backtrace();
        }

        return self::dump($trace);
    }

    /**
     * Dump information about variables, accepts any number of parameters, supports prefix-modifiers:
     *
     * ```
     *           |-------|----------------------------------------------|
     *           |       | Example:    `+ saged('magic');`              |
     *           |-------|----------------------------------------------|
     *           | !     | Dump ignoring depth limits for large objects |
     *           | print | Puts output into current DIR as sage.html    |
     *           | ~     | Simplifies sage output (rich->html->plain)   |
     *           | -     | Clean up any output before dumping           |
     *           | +     | Expand all nodes (in rich view)              |
     *           | @     | Return output instead of displaying it       |
     *           |-------|----------------------------------------------|
     * ```
     *
     * Modifiers are supported by all dump wrapper functions, including Dump::trace(). Combinations possible.
     *
     * -----
     * Shorthand to display debug_backtrace():
     *   Dump::dump( 1 );
     *   Dump::dump( debug_backtrace() ); // must be single parameter!
     *
     * @param mixed $data
     *
     * @return string|int returns 5463 (Dump in l33tspeak) if disabled
     *
     * Explanation for the magic number in return:
     *   The return value has to be an int otherwise modifiers throw typesafe warinings, eg if we return null:
     *
     *       ~d(); // TypeError: Cannot perform bitwise not on null
     *
     *   It's not zero because it doesn't matter and if you find this somewhere in your logs or something - you know who
     *   to blame :))
     */
    public static function dump($data = null)
    {
        try {
            $params = func_get_args();

            return call_user_func_array(array('Flames\Dump\Dump', 'doDump'), $params);
        } catch (Throwable $e) {
        } catch (Exception $e) {
        }

        return 5463;
    }

    public static function doDump($data = null)
    {
        $enabledMode = self::enabled();

        if (!$enabledMode) {
            return 5463;
        }

        self::_init();

        list($names, $modifiers, $callee, $previousCaller, $miniTrace) = self::_getCalleeInfo();

        // auto-detect mode if not explicitly set
        if ($enabledMode === true) {
            if (!empty($modifiers) && strpos($modifiers, 'print') !== false && isset($callee['file'])) {
                $newMode = self::MODE_RICH;
            } elseif (self::$outputFile && substr(self::$outputFile, -5) === '.html') {
                $newMode = self::MODE_RICH;
            } else {
                $newMode = PHP_SAPI === 'cli' && self::$cliDetection === true
                    ? self::MODE_CLI
                    : self::MODE_RICH;
            }

            if (self::$simplifyDisplay) {
                switch ($newMode) {
                    case self::MODE_RICH:
                        $newMode = self::MODE_PLAIN;
                        break;
                    case self::MODE_CLI:
                        $newMode = self::MODE_TEXT_ONLY;
                        break;
                }
            }

            if (!empty($modifiers) && strpos($modifiers, '~') !== false) {
                switch ($newMode) {
                    case self::MODE_RICH:
                        $newMode = self::MODE_PLAIN;
                        break;
                    case self::MODE_PLAIN:
                    case self::MODE_CLI:
                        $newMode = self::MODE_TEXT_ONLY;
                        break;
                }
            }

            self::enabled($newMode);
        }

        $decoratorClass = self::enabled() === self::MODE_RICH ? 'DumpDecoratorsRich' : 'DumpDecoratorsPlain';
        /** @var DumpDecoratorsPlain|DumpDecoratorsRich $decorator */

        if ($decoratorClass === 'DumpDecoratorsRich') {
            $decorator = new DumpDecoratorsRich();
        }
        elseif ($decoratorClass === 'DumpDecoratorsPlain') {
            $decorator = new DumpDecoratorsPlain();
        }

        $firstRunOldValue = $decorator->areAssetsNeeded();

        // process modifiers: @, +, !, ~ and -
        if (!empty($modifiers) && strpos($modifiers, '-') !== false) {
            $decorator->setAssetsNeeded(true);

            while (ob_get_level()) {
                ob_end_clean();
            }
        }
        if (!empty($modifiers) && strpos($modifiers, '+') !== false) {
            $expandedByDefaultOldValue = self::$expandedByDefault;
            self::$expandedByDefault = true;
        }
        if (!empty($modifiers) && strpos($modifiers, '!') !== false) {
            /*if (strpos($modifiers, '!!') !== false) {
                $oldClassNameBlacklist = self::$classNameBlacklist = array();
                $oldTraceBlacklist     = self::$traceBlacklist = array();
                $oldEnabledParsers     = self::$enabledParsers;

                self::$classNameBlacklist = array();
                self::$traceBlacklist     = array();
                if (($key = array_search('DumpParsersEloquent', self::$enabledParsers)) !== false) {
                    unset(self::$enabledParsers[$key]);
                }
            } else {*/
            $maxLevelsOldValue = self::$maxLevels;
            self::$maxLevels = false;
            /*}*/
        }
        if (!empty($modifiers) && strpos($modifiers, '@') !== false) {
            $returnOldValue = self::$returnOutput;
            self::$returnOutput = true;
        }
        if (self::$returnOutput) {
            if (self::$returnOutput === true) {
                $decorator->setAssetsNeeded(true);
            } elseif (!isset(self::$_openedOutput[self::$returnOutput])) {
                $decorator->setAssetsNeeded(true);

                self::$_openedOutput[self::$returnOutput] = true;
            }
        }

        if (!empty($modifiers) && strpos($modifiers, 'print') !== false && isset($callee['file'])) {
            $outputFileOldValue = self::$outputFile;
            self::$outputFile = dirname($callee['file']) . '/sage.html';
        }

        if (self::$outputFile && !isset(self::$_openedOutput[self::$outputFile])) {
            $firstRunOldValue = $decorator->areAssetsNeeded();

            $decorator->setAssetsNeeded(true);
        }

        $trace = false;
        $lightTrace = false;
        if (func_num_args() === 1) {
            if ($names === array('1') && $data === 1) {
                // Dump::dump(1) shorthand
                $trace = DumpHelper::php53orLater() ? debug_backtrace(true) : debug_backtrace();
            } elseif ($names === array('2') && $data === 2) {
                // Dump::dump(2) shorthand todo: create Dump::traceWithoutArgs()
                $lightTrace = true;
                $trace = debug_backtrace();
            } elseif (is_array($data)) {
                $trace = $data; // test if the single parameter is result of debug_backtrace()
            }
        }

        if ($trace) {
            $trace = self::_parseTrace($trace);
        }

        $output = '';
        if ($decorator->areAssetsNeeded()) {
            $output .= $decorator->init();
        }
        $output .= $decorator->wrapStart();

        if ($trace) {
            $output .= $decorator->decorateTrace($trace, $lightTrace);
        } else {
            if (func_num_args() === 0) {
                DumpParser::reset();
                $tmp = microtime();
                $varData = DumpParser::process($tmp, '');
                $varData->type = null;
                $varData->name = 'Dump called with no arguments';
                $varData->value = null;
                $varData->size = null;
                if (!empty($callee['function'])) {
                    if (!empty($callee['class']) && !empty($callee['type'])) {
                        $name = $callee['class'] . $callee['type'] . $callee['function'];
                    } else {
                        $name = $callee['function'];
                    }
                    $varData->name = $name . '( no parameters )';
                }
                $output .= $decorator->decorate($varData);
            } else {
                foreach (func_get_args() as $k => $argument) {
                    DumpParser::reset();
                    // when the dump arguments take long to generate output, user might have changed the file and
                    // Dump might not parse the arguments correctly, so check if names are set and while the
                    // displayed names might be wrong, at least don't throw an error
                    $output .= $decorator->decorate(
                        DumpParser::process($argument, empty($names[$k]) ? '???' : $names[$k])
                    );
                }
            }
        }

        $output .= $decorator->wrapEnd($callee, $miniTrace, $previousCaller);

        // now restore all on-the-fly settings and return

        if (self::$outputFile) {
            if (!isset(self::$_openedOutput[self::$outputFile])) {
                self::$_openedOutput[self::$outputFile] = fopen(self::$outputFile, 'w');
                $decorator->setAssetsNeeded($firstRunOldValue);
            }

            fwrite(self::$_openedOutput[self::$outputFile], $output);
        }

        self::enabled($enabledMode);

        $decorator->setAssetsNeeded(false);

        if (!empty($modifiers)) {
            if (strpos($modifiers, '~') !== false) {
                $decorator->setAssetsNeeded($firstRunOldValue);
            }

            if (strpos($modifiers, '+') !== false) {
                self::$expandedByDefault = $expandedByDefaultOldValue;
            }

            if (isset($maxLevelsOldValue)) {
                self::$maxLevels = $maxLevelsOldValue;
            }

            if (!empty($modifiers) && strpos($modifiers, 'print') !== false && isset($callee['file'])) {
                $tmp = self::$outputFile;
                self::$outputFile = $outputFileOldValue;

                if (strpos($modifiers, '@') === false) {
                    echo 'Dump -> ' . $tmp . PHP_EOL;
                }

                return 5463;
            }

            if (strpos($modifiers, '@') !== false) {
                self::$returnOutput = $returnOldValue;
                $decorator->setAssetsNeeded($firstRunOldValue);

                return $output;
            }
        }

        if (self::$returnOutput) {
            return $output;
        }

        if (self::$outputFile) {
            return 5463;
        }

        echo $output;

        return 5463;
    }


    /*
     *    ██████╗ ██████╗ ██╗██╗   ██╗ █████╗ ████████╗███████╗
     *    ██╔══██╗██╔══██╗██║██║   ██║██╔══██╗╚══██╔══╝██╔════╝
     *    ██████╔╝██████╔╝██║██║   ██║███████║   ██║   █████╗
     *    ██╔═══╝ ██╔══██╗██║╚██╗ ██╔╝██╔══██║   ██║   ██╔══╝
     *    ██║     ██║  ██║██║ ╚████╔╝ ██║  ██║   ██║   ███████╗
     *    ╚═╝     ╚═╝  ╚═╝╚═╝  ╚═══╝  ╚═╝  ╚═╝   ╚═╝   ╚══════╝
     *
     */

    /**
     * returns parameter names that the function was passed, as well as any predefined symbols before function
     * call (modifiers)
     *
     * @return array{$parameters, $modifier, $callee, $previousCaller}
     */
    private static function _getCalleeInfo()
    {
        $trace = debug_backtrace();
        $previousCaller = array();
        $miniTrace = array();
        $prevStep = array();
        $insideTemplateDetected = null;

        // go from back of trace forward to find first occurrence of call to Dump or its wrappers
        while ($step = array_pop($trace)) {
            if (DumpHelper::stepIsInternal($step)) {
                $previousCaller = $prevStep;
                break;
            }

            if (
                isset($step['args'][0])
                && is_string($step['args'][0])
                && substr($step['args'][0], -strlen('.blade.php')) === '.blade.php'
            ) {
                $insideTemplateDetected = $step['args'][0];
            }

            if (isset($step['file'], $step['line'])) {
                unset($step['object'], $step['args']);
                array_unshift($miniTrace, $step);
            }

            $prevStep = $step;
        }
        $callee = $step;

        if (!isset($callee['file']) || !is_readable($callee['file'])) {
            return array(null, null, $callee, $previousCaller, $miniTrace);
        }

        // open the file and read it up to the position where the function call expression ended
        // TODO since PHP 8.2 backtrace reports the lineno of the function/method name!
        // https://github.com/php/php-src/pull/8818
        //        $file = new SplFileObject($callee['file']);
        //        do {
        //            $file->seek($callee['line']);
        //            $contents = $file->current(); // $contents would hold the data from line x
        //
        //        } while (! $file->eof());

        $file = fopen($callee['file'], 'r');
        $line = 0;
        $source = '';
        while (($row = fgets($file)) !== false) {
            if (++$line > $callee['line']) {
                break;
            }
            $source .= $row;
        }
        fclose($file);
        $source = self::_removeAllButCode($source);

        if (empty($callee['class'])) {
            $codePattern = $callee['function'];
        } else {
            $codePattern = "\w+\x07*" . $callee['type'] . "\x07*" . $callee['function'];
        }

        // get the position of the last call to the function
        preg_match_all(
            "
            /
            # beginning of statement
            [\x07{(]

            # search for modifiers (group 1)
            ([print\x07-+!@~]*)?

            # spaces
            \x07*

            # possibly a namespace symbol
            \\\\?

            # spaces again
            \x07*

            # main call to Dump
            ({$codePattern})

            # spaces everywhere
            \x07*

            # find the character where Dump's opening bracket resides (group 3)
            (\\()

            /ix",
            $source,
            $matches,
            PREG_OFFSET_CAPTURE
        );

        $modifiers = end($matches[1]);
        $callToDump = end($matches[2]);
        $bracket = end($matches[3]);

        if (empty($callToDump)) {
            // if a wrapper is misconfigured, don't display the whole file as variable name
            return array(array(), $modifiers, $callee, $previousCaller, $miniTrace);
        }

        $modifiers = str_replace("\x07", '', $modifiers[0]);
        $paramsString = preg_replace("[\x07+]", ' ', substr($source, $bracket[1] + 1));
        // we now have a string like this:
        // <parameters passed>); <the rest of the last read line>

        // remove everything in brackets and quotes, we don't need nested statements nor literal strings which would
        // complicate separating individual arguments
        $c = strlen($paramsString);
        $inString = $escaped = $openedBracket = $closingBracket = false;
        $i = 0;
        $inBrackets = 0;
        $openedBrackets = array();
        $bracketPairs = array('(' => ')', '[' => ']', '{' => '}');

        while ($i < $c) {
            $letter = $paramsString[$i];

            if (!$inString) {
                if ($letter === '\'' || $letter === '"') {
                    $inString = $letter;
                } elseif ($letter === '(' || $letter === '[' || $letter === '{') {
                    $inBrackets++;
                    $openedBrackets[] = $openedBracket = $letter;
                    $closingBracket = $bracketPairs[$letter];
                } elseif ($inBrackets && $letter === $closingBracket) {
                    $inBrackets--;
                    array_pop($openedBrackets);
                    $openedBracket = end($openedBrackets);
                    if ($openedBracket) {
                        $closingBracket = $bracketPairs[$openedBracket];
                    }
                } elseif (!$inBrackets && $letter === ')') {
                    $paramsString = substr($paramsString, 0, $i);
                    break;
                }
            } elseif ($letter === $inString && !$escaped) {
                $inString = false;
            }

            // replace whatever was inside quotes or brackets with untypeable characters, we don't
            // need that info.
            if ($inBrackets > 0) {
                if ($inBrackets > 1 || $letter !== $openedBracket) {
                    $paramsString[$i] = "\x07";
                }
            }
            if ($inString) {
                if ($letter !== $inString || $escaped) {
                    $paramsString[$i] = "\x07";
                }
            }

            $escaped = !$escaped && ($letter === '\\');
            $i++;
        }

        $names = explode(',', preg_replace("[\x07+]", '...', $paramsString));
        $names = array_map('trim', $names);

        if ($insideTemplateDetected) {
            $callee['file'] = $insideTemplateDetected;
            $callee['line'] = null;
        }

        return array($names, $modifiers, $callee, $previousCaller, $miniTrace);
    }

    /**
     * removes comments and zaps whitespace & < ?php tags from php code, makes for easier further parsing
     *
     * @param string $source
     *
     * @return string
     */
    private static function _removeAllButCode($source)
    {
        $commentTokens = array(
            T_COMMENT => true,
            T_INLINE_HTML => true,
            T_DOC_COMMENT => true,
        );
        $whiteSpaceTokens = array(
            T_WHITESPACE => true,
            T_CLOSE_TAG => true,
            T_OPEN_TAG => true,
            T_OPEN_TAG_WITH_ECHO => true,
        );

        $cleanedSource = '';
        foreach (token_get_all($source) as $token) {
            if (is_array($token)) {
                if (isset($commentTokens[$token[0]])) {
                    continue;
                }

                if (isset($whiteSpaceTokens[$token[0]])) {
                    $token = "\x07";
                } else {
                    $token = $token[1];
                }
            } elseif ($token === ';') {
                $token = "\x07";
            }

            $cleanedSource .= $token;
        }

        return $cleanedSource;
    }

    private static function _parseTrace($data)
    {
        $trace = array();
        $traceFields = array('file', 'line', 'args', 'class');
        $fileFound = false; // file element must exist in one of the steps
        $lastStep = array();

        // validate whether a trace was indeed passed
        foreach ($data as $step) {
            if (!is_array($step) || !isset($step['function'])) {
                return false;
            }
            if (!$fileFound && isset($step['file']) && file_exists($step['file'])) {
                $fileFound = true;
            }

            $valid = false;
            foreach ($traceFields as $element) {
                if (isset($step[$element])) {
                    $valid = true;
                    break;
                }
            }
            if (!$valid) {
                return false;
            }

            if ($step['function'] === 'spl_autoload_call') { // meaningless
                continue;
            }

            // also modify it in the same go
            if (DumpHelper::stepIsInternal($step)) {
                // take first step from the top that is not inside Dump already
                if (isset($step['file'], $step['line'])) {
                    $lastStep = array(
                        'file' => $step['file'],
                        'line' => $step['line'],
                        'function' => '',
                    );
                }

                continue;
            }

            $trace[] = $step;
        }

        if (!$fileFound) {
            return false;
        }

        if ($lastStep) {
            array_unshift($trace, $lastStep);
        }

        // now parse the trace into a usable format
        $output = array();
        foreach ($trace as $i => $step) {
            $output[] = new DumpTraceStep($step, $i);
        }

        return $output;
    }

    /*
     *    ██╗███╗   ██╗██╗████████╗
     *    ██║████╗  ██║██║╚══██╔══╝
     *    ██║██╔██╗ ██║██║   ██║
     *    ██║██║╚██╗██║██║   ██║
     *    ██║██║ ╚████║██║   ██║
     *    ╚═╝╚═╝  ╚═══╝╚═╝   ╚═╝
     *
     */

    private static function _initSetting($name, $default)
    {
        if (!isset(self::$$name)) {
            $value = get_cfg_var('sage.' . $name);
            if (!$value) {
                $value = $default;
            }

            self::$$name = $value;
        }
    }

    private static $loadedParsers = 0;

    /** Called before each invocation */
    private static function _init()
    {
        DumpHelper::buildAliases();

        $parsersCount = 0;
        foreach (Dump::$enabledParsers as $enabled) {
            if ($enabled) {
                $parsersCount++;
            }
        }

        if (self::$loadedParsers !== $parsersCount) {
            self::$loadedParsers = $parsersCount;
            foreach (Dump::$enabledParsers as $className => $enabled) {
                if ($enabled && file_exists($f = DUMP_DIR . 'parsers/' . $className . '.php')) {
                    require_once $f;
                }
            }
        }

        if (self::$_initialized) {
            return;
        }

        // first load defaults for configuration. In this order:
        // 1. If value is set, it means user explicitly set it
        // 2. TODO: composer.json
        // 3. If present in get_cfg_var means user put it into his php.ini
        // 4. Load default from Dump
        self::_initSetting(
            'editor',
            ini_get('xdebug.file_link_format') ? ini_get('xdebug.file_link_format') : 'phpstorm-remote'
        );
        self::_initSetting('fileLinkServerPath', null);
        self::_initSetting('fileLinkLocalPath', null);
        self::_initSetting('displayCalledFrom', true);
        self::_initSetting('maxLevels', 7);
        self::_initSetting('theme', self::THEME_ORIGINAL);
        self::_initSetting('expandedByDefault', false);
        self::_initSetting('cliDetection', true);
        self::_initSetting('cliColors', true);
        self::_initSetting(
            'charEncodings',
            array(
                'UTF-8',
                'Windows-1252', // Western; includes iso-8859-1, replace this with windows-1251 if you have Russian code
                'euc-jp',       // Japanese
            )
        );
        self::_initSetting('returnOutput', false);
        self::_initSetting('aliases', array());
    }
}

if (get_cfg_var('sage.enabled') !== false) {
    Dump::enabled(get_cfg_var('sage.enabled'));
}
