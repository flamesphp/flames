<?php

namespace Flames\Cli\Command\Build;

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
    const BASE_PATH = (ROOT_PATH . 'App/Client/Resource/');

    protected static $defaultFiles = [
        'Flames/Kernel/Client.php',
        'Flames/Dump/Client.php',
        'Flames/Connection/Client.php',
        'Flames/Collection/Strings.php',
        'Flames/Collection/Arr.php',
        'Flames/Kernel/Wrapper/Raw.php',
        'Flames/Php.php',
        'Flames/Js.php',
        'Flames/Cli.php',
        'Flames/RequestData.php',
        'Flames/Kernel/Route.php',
        'Flames/Router.php',
        'Flames/Event/Route.php',
        'Flames/Router/Parser.php',
        'Flames/Header/Client.php',
        'Flames/Element.php',
        'Flames/Element/Event.php',
        'Flames/Http/Client/Client.php',
        'Flames/Http/Async/Request/Client.php',
        'Flames/Http/Async/Response/Client.php',
        'Flames/Event/Element/Click.php',
        'Flames/Event/Element/Change.php',
        'Flames/Event/Element/Input.php',
        'Flames/Kernel/Client/Dispatch.php'
    ];

    protected bool $debug = false;

    /**
     * Run the application build.
     *
     * @param bool $debug (optional) Determines whether the application should run in debug mode. Defaults to false.
     * @return bool Returns true if the application ran successfully, otherwise returns false.
     */
    public function run(bool $debug = false) : bool
    {
        $this->debug = $debug;

        $this->createFolder();

        $stream = fopen(self::BASE_PATH . 'client.js', "w");
        $this->injectStructure($stream);
        $this->injectDefaultFiles($stream).
        $this->injectClientFiles($stream).
        $this->injectEnvironment($stream).
        $this->finish($stream);

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

            mkdir(self::BASE_PATH, 0777, true);
            chmod(self::BASE_PATH, 0777);
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

        fputs($stream, '/*
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
        fputs($stream,"window.Flames = (window.Flames || {});Flames.Internal = (Flames.Internal || {});Flames.Internal.Build = (Flames.Internal.Build || {});Flames.Internal.Build.core = [];Flames.Internal.Build.client = [];Flames.Internal.Build.click = [];Flames.Internal.Build.change = [];Flames.Internal.Build.input = [];");
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
            $phpFile = file_get_contents(ROOT_PATH . $defaultFile);
            if ($defaultFile === 'Flames/Kernel/Client.php') {
                $phpFile = str_replace(['namespace Flames\Kernel;', 'final class Client'], ['namespace Flames;', 'final class Kernel'], $phpFile);
            } elseif ($defaultFile === 'Flames/Http/Client/Client.php') {
                $phpFile = str_replace('namespace Flames\Http\Client;', 'namespace Flames\Http;', $phpFile);
            } elseif ($defaultFile === 'Flames/Http/Async/Request/Client.php') {
                $phpFile = str_replace(['namespace Flames\Http\Async\Request;', 'class Client'], ['namespace Flames\Http\Async;', 'class Request'], $phpFile);
            } elseif ($defaultFile === 'Flames/Http/Async/Response/Client.php') {
                $phpFile = str_replace(['namespace Flames\Http\Async\Response;', 'class Client'], ['namespace Flames\Http\Async;', 'class Response'], $phpFile);
            } elseif ($defaultFile === 'Flames/Connection/Client.php') {
                $phpFile = str_replace(['namespace Flames\Connection;', 'class Client'], ['namespace Flames;', 'class Connection'], $phpFile);
            } elseif ($defaultFile === 'Flames/Header/Client.php') {
                $phpFile = str_replace(['namespace Flames\Header;', 'class Client'], ['namespace Flames;', 'class Header'], $phpFile);
            }

            if ($this->debug === true) {
                echo ('Compile ' . substr($defaultFile, 0, -4) . "");
            }

            fputs($stream, ('Flames.Internal.Build.core[Flames.Internal.Build.core.length] = \'' .
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
            $clientPath = (ROOT_PATH . 'App/Client/' . $module);
            if (is_dir($clientPath) === true) {
                $files = $this->getDirContents($clientPath);
                foreach ($files as $file) {
                    if (is_dir($file) === true) {
                        continue;
                    }

                    if ($module === 'Controller') {
                        $attributes = $this->verifyAttributes($file);
                        foreach ($attributes->click as $trigger) {
                            fputs($stream, ('Flames.Internal.Build.click[\'' . $trigger->uid . '\'] = [\'' . urlencode($trigger->class) . '\',\'' . $trigger->name . "'];"));
                        }
                        foreach ($attributes->change as $trigger) {
                            fputs($stream, ('Flames.Internal.Build.change[\'' . $trigger->uid . '\'] = [\'' . urlencode($trigger->class) . '\',\'' . $trigger->name . "'];"));
                        }
                        foreach ($attributes->input as $trigger) {
                            fputs($stream, ('Flames.Internal.Build.input[\'' . $trigger->uid . '\'] = [\'' . urlencode($trigger->class) . '\',\'' . $trigger->name . "'];"));
                        }
                    }

                    if ($this->debug === true) {
                        echo ('Compile module ' . strtolower($module) . ': ' . substr($file, strlen(ROOT_PATH), -4) . "\n");
                    }

                    fputs($stream, ('Flames.Internal.Build.client[Flames.Internal.Build.client.length] = [\'' .
                            substr($file, strlen(ROOT_PATH), -4) . '\', \'' .
                            base64_encode(file_get_contents($file))) . "'];");
                }
            }
        }
    }

    /**
     * Verifies the attributes of a given file.
     *
     * @param string $file The file to verify attributes for.
     * @return array
     */
    protected function verifyAttributes(string $file)
    {
        $class = (str_replace('/', '\\', substr($file, strlen(ROOT_PATH), -4)));
        $data = Data::mountData($class);

        $attributes = Arr([
            'click'  => Arr(),
            'change' => Arr(),
            'input'  => Arr()
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
        fputs($stream, "Flames.Internal.dumpLocalPath='" . $localPath . "';"
        );
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
        fputs($stream, "\n\n");
        fclose($stream);

        if ($this->debug === true) {
            echo ("\nAssets build successfully\n");
        }
    }
}