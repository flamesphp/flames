<?php

namespace Flames\Router;
use Flames\Command;
use Flames\Environment;

/***
 * @internal
 */
class Client
{
    public static function run(string $uri) : bool
    {
        $uri = substr($uri, 9);
        $uri = explode('?', $uri)[0];

        if ($uri === 'js') {
            return self::dispatchFlames();
        }
        elseif ($uri === 'wasm') {
            return self::dispatchFlamesWasm();
        }
        elseif ($uri === 'png') {
            return self::dispatchFlamesPng();
        }
        elseif ($uri === 'auto/style') {
            return self::dispatchFlamesAutoStyle();
        }

        return false;
    }

    protected static function dispatchFlames() : bool
    {
        header('Cache-Control: max-age=31536000');
        header('Content-Type: application/javascript; charset=utf-8');

        $clientPath = (APP_PATH . 'Client/Resource/client.js');
        if (file_exists($clientPath) === true) {
            $fileStream = fopen($clientPath, 'r');
            while(!feof($fileStream)) {
                $buffer = fgets($fileStream, 1024000); // 128 kb
                echo $buffer;
                ob_flush();
                flush();
            }
            fclose($fileStream);

        }

        $fileStream = fopen(FLAMES_PATH . 'Kernel/Client/Engine/Flames.js', 'r');
        while(!feof($fileStream)) {
            $buffer = fgets($fileStream, 1024000); // 128 kb
            echo $buffer;
            ob_flush();
            flush();
        }
        fclose($fileStream);

        return true;
    }

    protected static function dispatchFlamesWasm()
    {
        header('Cache-Control: max-age=31536000');
        header('Content-Type: application/wasm');
        $fileStream = fopen(FLAMES_PATH . 'Kernel/Client/Engine/Flames.wasm', 'r');

        while(!feof($fileStream)) {
            $buffer = fread($fileStream, 1024000); // 1 mb
            echo $buffer;
            ob_flush();
            flush();
        }

        return true;
    }

    protected static function dispatchFlamesPng()
    {
        header('Cache-Control: max-age=31536000');
        header('Content-Type: image/png');
        $fileStream = fopen(FLAMES_PATH . 'Kernel/Client/Engine/Flames.png', 'r');

        while(!feof($fileStream)) {
            $buffer = fread($fileStream, 1024000); // 1 mb
            echo $buffer;
            ob_flush();
            flush();
        }

        return true;
    }

    protected static function dispatchFlamesAutoStyle()
    {
        if (Environment::get('AUTO_BUILD_CLIENT') !== true) {
            return false;
        }

        Command::run('build:assets --auto');
    }
}