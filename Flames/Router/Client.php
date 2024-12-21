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
        $uri = substr($uri, 8);
        $uri = explode('?', $uri)[0];

        if ($uri === 'js') {
            return self::dispatchFlames();
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

        $clientPath = (APP_PATH . 'Client/Resource/Build/Flames.js');
        if (file_exists($clientPath) === true) {
            $fileStream = fopen($clientPath, 'r');
            while(!feof($fileStream)) {
                $buffer = fgets($fileStream, 1024000); // 1mb
                echo $buffer;
                ob_flush();
                flush();
            }
            fclose($fileStream);

        }

        return true;
    }

    protected static function dispatchFlamesAutoStyle(): bool
    {
        if (Environment::get('CLIENT_AUTO_BUILD') !== true) {
            return false;
        }

        Command::run('build:assets --auto');
        return true;
    }
}