<?php

namespace Flames\Cli\Command\Build;

use Flames\Cli;
use Flames\Cli\Command\Build\Assets\Automate;
use Flames\Cli\Command\Build\Assets\Data;
use Flames\Environment;

/**
 * Class Assets
 *
 * This class is responsible for handling the build assets data for the Flames CLI command.
 *
 * @internal
 */
final class Assets
{
    const BASE_PATH = (APP_PATH . 'Client/Resource/');

    protected static $defaultFiles = [
        'Kernel/Client.php',
        'Dump/Client.php',
        'Connection/Client.php',
        'Collection/Strings.php',
        'Collection/Bools.php',
        'Collection/Ints.php',
        'Collection/Arr.php',
        'Kernel/Wrapper/Raw.php',
        'Php.php',
        'Js.php',
        'Cli.php',
        'RequestData.php',
        'Kernel/Route.php',
        'Browser/Page.php',
        'Router.php',
        'Json.php',
        'Event/Route.php',
        'Router/Parser.php',
        'Header/Client.php',
        'Coroutine/Timeout.php',
        'Coroutine/Timeout/Event.php',
        'Element.php',
        'Element/Event.php',
        'Money/Client.php',
        'Http/Client/Client.php',
        'Http/Async/Request/Client.php',
        'Http/Async/Response/Client.php',
        'Http/Code.php',
        'Event/Element/Click.php',
        'Event/Element/Change.php',
        'Event/Element/Input.php',
        'Kernel/Client/Dispatch.php'
    ];

    protected bool $debug = false;
    protected bool $auto = false;

    public function __construct($data)
    {
        if ($data->option->contains('auto') === true) {
            $this->auto = true;
        }
    }
    /**
     * Run the application build.
     *
     * @param bool $debug (optional) Determines whether the application should run in debug mode. Defaults to false.
     * @return bool Returns true if the application ran successfully, otherwise returns false.
     */
    public function run(bool $debug = false) : bool
    {
        // Skip build if re-request from client after timeout
        if ($this->auto === true) {
            if (Cli::isCli() === false && isset($_GET['timeout']) === true && $_GET['timeout'] === 'true') {
                $this->verifyAuto();
                return false;
            }
        }

        $this->debug = $debug;

        $this->createFolder();

        try {
            $stream = fopen(self::BASE_PATH . 'client.js', 'w');
        } catch (\Exception $e) {
            $mask = umask(0);
            @mkdir(self::BASE_PATH, 0777, true);
            umask($mask);
            $stream = fopen(self::BASE_PATH . 'client.js', 'w');
        }

        $this->injectStructure($stream);
        $this->injectDefaultFiles($stream).
        $this->injectClientFiles($stream).
        $this->injectEnvironment($stream).
        $this->finish($stream);
        $this->verifyAuto();

        return true;
    }

    /**
     * Create the base resource folder if it doesn't exist.
     *
     * @return void
     */
    protected function createFolder() : void
    {
        if ($this->debug === true) {
            echo ('Verifying base resource folder ' . substr(self::BASE_PATH, strlen(ROOT_PATH)) . "\n");
        }

        if (is_dir(self::BASE_PATH) === false) {
            if ($this->debug === true) {
                echo ('Creating base resource folder ' . self::BASE_PATH . "\n");
            }

            $mask = umask(0);
            mkdir(self::BASE_PATH, 0777, true);
            umask($mask);
        }
    }

    /**
     * Injects the structure of the JavaScript system into the given stream.
     *
     * @param resource $stream The stream to inject the structure into.
     * @return void
     */
    protected function injectStructure($stream) : void
    {
        if ($this->debug === true) {
            echo ("Inject structure javascript system\n");
        }

        fwrite($stream, '/*
    ███████╗██╗      █████╗ ███╗   ███╗███████╗███████╗
    ██╔════╝██║     ██╔══██╗████╗ ████║██╔════╝██╔════╝
    █████╗  ██║     ███████║██╔████╔██║█████╗  ███████╗
    ██╔══╝  ██║     ██╔══██║██║╚██╔╝██║██╔══╝  ╚════██║
    ██║     ███████╗██║  ██║██║ ╚═╝ ██║███████╗███████║
    ╚═╝     ╚══════╝╚═╝  ╚═╝╚═╝     ╚═╝╚══════╝╚══════╝
    
    𝗖𝗿𝗲𝗮𝘁𝗲𝗱 𝗯𝘆 𝗚𝗮𝗯𝗿𝗶𝗲𝗹 \'𝗞𝗮𝘇𝘇\' 𝗠𝗼𝗿𝗴𝗮𝗱𝗼
    Github: https://github.com/flamesphp/flames
    Docs:   https://flamesphp.com
    
*/

');
        fwrite($stream,"window.Flames = (window.Flames || {});Flames.Internal = (Flames.Internal || {});Flames.Internal.Build = (Flames.Internal.Build || {});Flames.Internal.Build.core = [];Flames.Internal.Build.client = [];Flames.Internal.Build.click = [];Flames.Internal.Build.staticConstruct = [];Flames.Internal.Build.change = [];Flames.Internal.Build.input = [];");
    }

    /**
     * Injects default files into the provided stream.
     *
     * @param resource $stream The stream to inject the files into.
     * @return void
     */
    protected function injectDefaultFiles($stream) : void
    {
        foreach (self::$defaultFiles as $defaultFile) {
            $phpFile = file_get_contents(FLAMES_PATH . $defaultFile);
            if ($defaultFile === 'Kernel/Client.php') {
                $phpFile = str_replace(['namespace Flames\Kernel;', 'final class Client'], ['namespace Flames;', 'final class Kernel'], $phpFile);
            } elseif ($defaultFile === 'Http/Client/Client.php') {
                $phpFile = str_replace('namespace Flames\Http\Client;', 'namespace Flames\Http;', $phpFile);
            } elseif ($defaultFile === 'Http/Async/Request/Client.php') {
                $phpFile = str_replace(['namespace Flames\Http\Async\Request;', 'class Client'], ['namespace Flames\Http\Async;', 'class Request'], $phpFile);
            } elseif ($defaultFile === 'Http/Async/Response/Client.php') {
                $phpFile = str_replace(['namespace Flames\Http\Async\Response;', 'class Client'], ['namespace Flames\Http\Async;', 'class Response'], $phpFile);
            } elseif ($defaultFile === 'Connection/Client.php') {
                $phpFile = str_replace(['namespace Flames\Connection;', 'class Client'], ['namespace Flames;', 'class Connection'], $phpFile);
            } elseif ($defaultFile === 'Header/Client.php') {
                $phpFile = str_replace(['namespace Flames\Header;', 'class Client'], ['namespace Flames;', 'class Header'], $phpFile);
            } elseif ($defaultFile === 'Money/Client.php') {
                $phpFile = str_replace(['namespace Flames\Money;', 'class Client'], ['namespace Flames;', 'class Money'], $phpFile);
            }

            if ($this->debug === true) {
                echo ('Compile ' . substr($defaultFile, 0, -4) . "\n");
            }

            fwrite($stream, ('Flames.Internal.Build.core[Flames.Internal.Build.core.length] = \'' .
                    base64_encode($phpFile)) . "';");
        }
    }

    /**
     * Injects client files into the specified stream.
     *
     * @param resource $stream The stream where the client files will be injected.
     *
     * @return void
     */
    protected function injectClientFiles($stream) : void
    {
        $modules = ['Event', 'Component', 'Controller'];
        foreach ($modules as $module) {
            $clientPath = (APP_PATH . 'Client/' . $module);
            if (is_dir($clientPath) === true) {
                $files = $this->getDirContents($clientPath);
                foreach ($files as $file) {
                    if (is_dir($file) === true) {
                        continue;
                    }

                    if ($module === 'Controller' || $module === 'Component') {

                        $class = (str_replace('/', '\\', substr($file, strlen(ROOT_PATH), -4)));
                        $data = Data::mountData($class);
                        $attributes = $this->verifyAttributes($data, $class);

                        foreach ($attributes->click as $trigger) {
                            fwrite($stream, ('Flames.Internal.Build.click[\'' . $trigger->uid . '\'] = [\'' . urlencode($trigger->class) . '\',\'' . $trigger->name . "'];"));
                        }
                        foreach ($attributes->change as $trigger) {
                            fwrite($stream, ('Flames.Internal.Build.change[\'' . $trigger->uid . '\'] = [\'' . urlencode($trigger->class) . '\',\'' . $trigger->name . "'];"));
                        }
                        foreach ($attributes->input as $trigger) {
                            fwrite($stream, ('Flames.Internal.Build.input[\'' . $trigger->uid . '\'] = [\'' . urlencode($trigger->class) . '\',\'' . $trigger->name . "'];"));
                        }

                        if ($data->staticConstruct === true) {
                            fwrite($stream, ('Flames.Internal.Build.staticConstruct[Flames.Internal.Build.staticConstruct.length] = \'' . urlencode($class) . "';"));
                        }
                    }

                    if ($this->debug === true) {
                        echo ('Compile module ' . strtolower($module) . ': ' . substr($file, strlen(ROOT_PATH), -4) . "\n");
                    }

                    fwrite($stream, ('Flames.Internal.Build.client[Flames.Internal.Build.client.length] = [\'' .
                            substr($file, strlen(ROOT_PATH), -4) . '\', \'' .
                            base64_encode(@utf8_decode(file_get_contents($file)))) . "'];");
                }

            }
        }
    }

    /**
     * Verifies the attributes of a given file.
     *
     * @param array $data The data to verify attributes for.
     * @param string $class The class to verify attributes for.
     * @return array
     */
    protected function verifyAttributes($data, string $class)
    {
        $attributes = Arr([
            'click'  => Arr(),
            'change' => Arr(),
            'input'  => Arr(),
        ]);

        foreach ($data->methods as $method) {
            if ($method->type === 'click') {
                $method->class = $class;
                $attributes->click[] = $method;
            }
            elseif ($method->type === 'change') {
                $method->class = $class;
                $attributes->change[] = $method;
            }
            elseif ($method->type === 'input') {
                $method->class = $class;
                $attributes->input[] = $method;
            }
        }

        return $attributes;
    }

    /**
     * Retrieves the contents of a directory recursively.
     *
     * @param string $dir The directory to retrieve contents from.
     * @param array $results (Optional) An array reference to store the results. Defaults to an empty array.
     *
     * @return void
     */
    protected function getDirContents($dir, &$results = array()) {
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[] = $path;
            } else if ($value !== '.' && $value !== '..') {
                $this->getDirContents($path, $results);
                $results[] = $path;
            }
        }

        return $results;
    }

    protected function injectEnvironment($stream)
    {
        $localPath = Environment::get('DUMP_LOCAL_PATH');
        if (str_ends_with($localPath, '\\') || str_ends_with($localPath, '/')) {
            $localPath = substr($localPath, 0, -1);
        }
        if (str_contains($localPath, '\\') === true) {
            $localPath = str_replace('\\', '\\\\', $localPath);
        }
        fwrite($stream, "Flames.Internal.dumpLocalPath='" . $localPath . "';");
        $autoBuildClient = Environment::get('AUTO_BUILD_CLIENT');
        if ($autoBuildClient === true) {
            fwrite($stream, "Flames.Internal.autoBuildClient=true;");
        } else {
            fwrite($stream, "Flames.Internal.autoBuildClient=false;");
        }
    }

    /**
     * Finishes the stream by closing it.
     *
     * @param resource $stream The stream to be finished.
     *
     * @return void
     */
    protected function finish($stream) : void
    {
        fwrite($stream, "\n\n");
        fclose($stream);

        if ($this->debug === true) {
            echo ("\nAssets build successfully\n");
        }
    }

    protected function verifyAuto()
    {
        if ($this->auto === true) {
            $automate = new Automate();
            $automate->run($this->debug);
        }
    }
}