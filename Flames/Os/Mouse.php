<?php

namespace Flames\Os;

use Exception;
use Flames\Collection\Arr;
use Flames\Crypto\Hash;
use Flames\OS;

class Mouse
{
    const CLICK_LEFT   = 'left';
    const CLICK_RIGHT  = 'right';
    const CLICK_MIDDLE = 'middle';

    public static function getPosition() : Arr
    {
        if (Os::isUnix() === true) {
            throw new Exception('Unix not supported yet.');
        }

        $execPath = str_replace('/', '\\', (FLAMES_PATH . 'OS/Mouse/GetMousePosition.ps1'));
        $data = (shell_exec('powershell.exe -File ' . $execPath));
        $split = explode(' ', $data);

        return Arr([
            'x' => (int)$split[count($split) - 2],
            'y' => (int)$split[count($split) - 1]
        ]);
    }

    public static function setPosition(int $x, int $y, bool $leftClick = false)
    {
        if (Os::isUnix() === true) {
            throw new Exception('Unix not supported yet.');
        }


        $tmpData = file_get_contents(str_replace('/', '\\', (FLAMES_PATH . 'OS/Mouse/SetMousePosition.ps1')));
        $tmpData = str_replace(['$x', '$y'], [$x, $y], $tmpData);

        if ($leftClick === true) {
            $tmpData .= ("\n" . file_get_contents(str_replace('/', '\\', (FLAMES_PATH . 'OS/Mouse/LeftClickMouseJoin.ps1'))));
        }

        $tmpPath = str_replace('/', '\\', (ROOT_PATH . '.cache/powershell/' . Hash::getRandom() . '.ps1'));
        $tmpDir = dirname($tmpPath);
        if (is_dir($tmpDir) === false) {
            mkdir($tmpDir, 0777, true);
        }
        @file_put_contents($tmpPath, $tmpData);
        shell_exec('powershell.exe -File ' . $tmpPath);
        unlink($tmpPath);
    }

    public static function click(string $type = 'left')
    {
        if (Os::isUnix() === true) {
            throw new Exception('Unix not supported yet.');
        }

        if ($type !== self::CLICK_LEFT) {
            throw new Exception('Click ' . $type . ' not supported yet.');
        }

        $execPath = str_replace('/', '\\', (FLAMES_PATH . 'OS/Mouse/LeftClickMouse.ps1'));
        shell_exec('powershell.exe -File ' . $execPath);
    }
}