<?php

namespace Flames\Kernel\Client;

use Flames\Kernel\Client\Build\Data;

class Build
{
    const BASE_PATH = (ROOT_PATH . 'App/Client/Resource/');

    protected static $defaultFiles = [
        'Flames/Kernel/Client.php',
        'Flames/Dump/Client.php',
        'Flames/Collection/Strings.php',
        'Flames/Collection/Arr.php',
        'Flames/Kernel/Wrapper/Raw.php',
        'Flames/PHP.php',
        'Flames/JS.php',
        'Flames/CLI.php',
        'Flames/RequestData.php',
        'Flames/Kernel/Route.php',
        'Flames/Router.php',
        'Flames/Event/Route.php',
        'Flames/Router/Parser.php',
        'Flames/Element.php',
        'Flames/Http/Client/Client.php',
        'Flames/Http/Async/Request/Client.php',
        'Flames/Http/Async/Response/Client.php',
        'Flames/Kernel/Client/Dispatch.php',
    ];

    public function run()
    {
        $this->createFolder();

        $stream = fopen(self::BASE_PATH . 'client.js', "w");
        $this->injectStructure($stream);
        $this->injectDefaultFiles($stream).
        $this->injectClientFiles($stream).
        $this->finish($stream);
    }

    protected function createFolder() : void
    {
        if (is_dir(self::BASE_PATH) === false) {
            mkdir(self::BASE_PATH, 0777, true);
            chmod(self::BASE_PATH, 0777);
        }
    }

    protected function injectStructure($stream) : void
    {
        fputs($stream,"window.Flames = (window.Flames || {});\nFlames.Internal = (Flames.Internal || {});\nFlames.Internal.Build = (Flames.Internal.Build || {});\nFlames.Internal.Build.core = [];\nFlames.Internal.Build.client = [];\nFlames.Internal.Build.click = [];\n");
    }

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
            }

            fputs($stream, ('Flames.Internal.Build.core[Flames.Internal.Build.core.length] = \'' .
                base64_encode($phpFile)) . "';\n");
        }
    }

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
                        foreach ($attributes->click as $clickTrigger) {
                            fputs($stream, ('Flames.Internal.Build.click[\'' . $clickTrigger->uid . '\'] = [\'' . urlencode($clickTrigger->class) . '\',\'' . $clickTrigger->name . "'];\n"));
                        }
                    }

                    fputs($stream, ('Flames.Internal.Build.client[Flames.Internal.Build.client.length] = [\'' .
                        substr($file, strlen(ROOT_PATH), -4) . '\', \'' .
                        base64_encode(file_get_contents($file))) . "'];\n");
                }
            }
        }
    }

    protected function verifyAttributes(string $file)
    {
        $class = (str_replace('/', '\\', substr($file, strlen(ROOT_PATH), -4)));
        $data = Data::mountData($class);

        $attributes = Arr(['click' => Arr()]);

        foreach ($data->methods as $method) {
            if ($method->type === 'click') {
                $method->class = $class;
                $attributes->click[] = $method;
            }
        }

        return $attributes;
    }

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

    protected function finish($stream) : void
    {
        fputs($stream, "\n\n");
        fclose($stream);
    }
}