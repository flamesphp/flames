<?php

namespace Flames\Router;
/***
 * @internal
 */
class Client
{
    public static function run(string $uri) : bool
    {
        $uri = substr($uri, 9);

        if ($uri === 'js') {
            return self::dispatchFlames();
        }
        elseif ($uri === 'wasm') {
            return self::dispatchFlamesWASM();
        }

        return false;
    }

    protected static function dispatchFlames() : bool
    {
        header('Content-Type: application/javascript; charset=utf-8');

        $clientPath = (ROOT_PATH . 'App/Client/Resource/client.js');
        if (file_exists($clientPath) === true) {
            $fileStream = fopen($clientPath, 'r');
            while(!feof($fileStream)) {
                $buffer = fgets($fileStream, 128000); // 128 kb
                echo $buffer;
                ob_flush();
                flush();
            }
            fclose($fileStream);

        }

        $fileStream = fopen(ROOT_PATH . 'Flames/Kernel/Client/Engine/Flames.js', 'r');
        while(!feof($fileStream)) {
            $buffer = fgets($fileStream, 128000); // 128 kb
            echo $buffer;
            ob_flush();
            flush();
        }
        fclose($fileStream);

        return true;
    }

    protected static function dispatchFlamesWASM()
    {
        header('Cache-Control: max-age=31536000');
        header('Content-Type: application/wasm');
        $fileStream = fopen(ROOT_PATH . 'Flames/Kernel/Client/Engine/Flames.wasm', 'r');

        while(!feof($fileStream)) {
            $buffer = fread($fileStream, 1024000); // 1 mb
            echo $buffer;
            ob_flush();
            flush();
        }

        return true;
    }
}