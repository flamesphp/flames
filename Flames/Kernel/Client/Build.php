<?php

namespace Flames\Kernel\Client;

use RecursiveDirectoryIterator;
use RecursiveTreeIterator;

class Build
{
    const BASE_PATH = (ROOT_PATH . 'App/Client/Resource/');

    protected static $defaultFiles = [
        'Flames/Kernel/Client/Engine/Kernel.phpc',
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
        'Flames/Kernel/Client.php',
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
        fputs($stream,"window.Flames = (window.Flames || {});\nFlames.Internal = (Flames.Internal || {});\nFlames.Internal.Build = (Flames.Internal.Build || {});\nFlames.Internal.Build.core = [];\nFlames.Internal.Build.client = [];\n");
    }

    protected function injectDefaultFiles($stream) : void
    {
        foreach (self::$defaultFiles as $defaultFile) {
            fputs($stream, ('Flames.Internal.Build.core[Flames.Internal.Build.core.length] = \'' .
                base64_encode(file_get_contents(ROOT_PATH . $defaultFile))) . "';\n");
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
                    fputs($stream, ('Flames.Internal.Build.client[Flames.Internal.Build.client.length] = [\'' .
                        substr($file, strlen(ROOT_PATH), -4) . '\', \'' .
                        base64_encode(file_get_contents($file))) . "'];\n");
                }
            }
        }
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