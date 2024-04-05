<?php

namespace Flames;

use Flames\Cryptography\Hash;
use Flames\Http\Client;
use Flames\Http\Async\Request;
use GdImage;

class Image
{
    const FORMAT_PNG = 'png';
    const FORMAT_JPG = 'jpg';

    protected string|null $path;
    protected GdImage|null $image;

    public function __construct(string|null $path = null)
    {
        $this->path = $path;

        if ($this->path !== null) {
            $imageData = file_get_contents($this->path );
            $imageGd = imagecreatefromstring($imageData);
            if ($imageGd !== null) {
                $this->image = $imageGd;
            }
        }
    }

    public function show(string $format = self::FORMAT_PNG) : void
    {
        if ($this->image === null) {
            return;
        }

        if ($format === self::FORMAT_PNG) {
            Header::set('Content-Type', 'image/png');
            Header::send();
            imagepng($this->image);
        }
        elseif ($format === self::FORMAT_JPG) {
            Header::set('Content-Type', 'image/jpg');
            Header::send();
            imagejpeg($this->image);
        }
        else {
            imagegd($this->image);
        }
    }

    public function getString() : string|null
    {
        if ($this->image === null) {
            return null;
        }

        ob_start();
        imagepng($this->image);
        $buffer = ob_get_contents();
        ob_end_clean();

        return $buffer;
    }

    public function crop(int $x, int $y, int $width, int $height)
    {
        if ($this->image === null) {
            return;
        }

        $this->image = imagecrop($this->image, ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height]);
    }

    public function getText() : string|null
    {
        $tmpDir = (ROOT_PATH . '.cache/ocr-image/');
        if (is_dir($tmpDir) === false) {
            mkdir($tmpDir, 0777, true);
        }

        $imageHash = Hash::getRandom();
        $tmpPathPng = ($tmpDir . $imageHash . '.png');
        $tmpPathTxt = ($tmpDir . $imageHash . '.txt');
        $this->save($tmpPathPng, 'png');

        chdir($tmpDir);
        exec('tesseract ' . $imageHash . '.png ' . $imageHash . ' --psm 7');
        chdir(ROOT_PATH);
        $ocrData = @file_get_contents($tmpDir . $imageHash . '.txt');
        unlink($tmpPathPng);
        unlink($tmpPathTxt);
        if ($ocrData === false) {
            return null;
        }
        return trim($ocrData);
    }

    public function save(string $path, string $format = self::FORMAT_PNG)
    {
        ob_start();

        if ($format === self::FORMAT_PNG) {
            imagepng($this->image);
        }
        elseif ($format === self::FORMAT_JPG) {
            imagejpeg($this->image);
        }
        else {
            imagegd($this->image);
        }

        $buffer = ob_get_contents();
        ob_end_clean();

        try {
            file_put_contents($path, $buffer);
            return true;
        } catch (\ErrorException $e) {}

        $dir = dirname($path);
        if (is_dir($dir) === false) {
            mkdir($dir, 0777, true);
        }

        try {
            file_put_contents($path, $buffer);
            return true;
        } catch (\ErrorException $e) {}

        return false;
    }

    public static function fromUrl(string $url) : Image|null
    {
        $client = new Client(['defaults' => [
            'verify' => false
        ]]);
        $response = $client->request('GET', $url);

        if ($response->getStatusCode() !== 200) {
            return null;
        }

        $gdImage = (@imagecreatefromstring($response->getBody()));
        if ($gdImage === false) {
            return null;
        }

        $image = new Image();
        $image->image = $gdImage;
        return $image;
    }

    public static function fromString(string $string) : Image|null
    {
        $gdImage = (@imagecreatefromstring($string));
        if ($gdImage === false) {
            return null;
        }

        $image = new Image();
        $image->image = $gdImage;
        return $image;
    }
}