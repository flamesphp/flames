<?php

namespace Flames\Router;
use Flames\Cli\Command\Build\Assets\Automate;
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
        elseif ($uri === 'auto/build') {
            return self::dispatchFlamesAutoBuild();
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

    protected static function dispatchFlamesAutoBuild(): bool
    {
        if (Environment::get('AUTO_BUILD_CLIENT') !== true) {
            return false;
        }

        $automate = new Automate();
        $currentHash = $automate->getCurrentHash();

        $data = json_decode(file_get_contents('php://input'), true);
        $checkHash = @$data['hash'];

        if ($checkHash === $currentHash) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['changed' => false]);
            exit;
        }

        Command::run('build:assets --auto');
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['changed' => true]);
        exit;
    }
}