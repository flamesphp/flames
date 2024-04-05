<?php

namespace Flames\OS\Screen;

use Exception;
use Flames\Cryptography\Hash;
use Flames\OS;
use Flames\Image as ImageEx;

class Image
{
    public static function take()
    {
        if (OS::isUnix() === true) {
            throw new Exception('Unix not supported yet.');
        }

        $tmpDir = (ROOT_PATH . '.cache\\os-image\\');
        if (is_dir($tmpDir) === false) {
            mkdir($tmpDir, 0777, true);
        }

        chdir($tmpDir);
        $batPath = str_replace('/', '\\', (ROOT_PATH . 'Flames\OS\Screen\Image\Take.bat'));
        $imageHash = Hash::getRandom();
        exec($batPath . ' ' . $imageHash . '.png');
        chdir(ROOT_PATH);

        $imagePath = ($tmpDir . $imageHash . '.png');
        if (file_exists($imagePath) === false) {
            return null;
        }

        $imageData = file_get_contents($imagePath);
        $image = ImageEx::fromString($imageData);
        unlink($imagePath);
        return $image;
    }
}