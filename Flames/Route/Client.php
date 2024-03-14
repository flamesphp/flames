<?php

namespace Flames\Route;
/***
 * @internal
 */
class Client
{
    public static function run(string $uri) : bool
    {
        $uri = substr($uri, 2);

        if ($uri === 'flames.js') {
            return self::dispatchFlames();
        }
        elseif ($uri === 'flames/kernel.mjs') {
            return self::dispatchKernel();
        }
        elseif ($uri === 'flames/kernel/base.mjs') {
            return self::dispatchKernelBase();
        }
        elseif ($uri === 'flames/kernel/web.mjs') {
            return self::dispatchKernelWeb();
        }
        elseif ($uri === 'flames/kernel/web.wasm') {
            return self::dispatchKernelWebAssembly();
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

    protected static function dispatchKernel() : bool
    {
        header('Content-Type: application/javascript; charset=utf-8');
        $fileStream = fopen(ROOT_PATH . 'Flames/Kernel/Client/Engine/Flames/Kernel.mjs', 'r');

        while(!feof($fileStream)) {
            $buffer = fgets($fileStream, 128000); // 128 kb
            echo $buffer;
            ob_flush();
            flush();
        }

        return true;
    }

    protected static function dispatchKernelBase() : bool
    {
        header('Content-Type: application/javascript; charset=utf-8');
        $fileStream = fopen(ROOT_PATH . 'Flames/Kernel/Client/Engine/Flames/Kernel/Base.mjs', 'r');

        while(!feof($fileStream)) {
            $buffer = fgets($fileStream, 128000); // 128 kb
            echo $buffer;
            ob_flush();
            flush();
        }

        return true;
    }

    protected static function dispatchKernelWeb() : bool
    {
        header('Content-Type: application/javascript; charset=utf-8');
        $fileStream = fopen(ROOT_PATH . 'Flames/Kernel/Client/Engine/Flames/Kernel/web.mjs', 'r');

        while(!feof($fileStream)) {
            $buffer = fgets($fileStream, 128000); // 128 kb
            echo $buffer;
            ob_flush();
            flush();
        }

        return true;
    }

    protected static function dispatchKernelWebAssembly() : bool
    {
        header('Cache-Control: max-age=31536000');
        header('Content-Type: application/wasm');
        $fileStream = fopen(ROOT_PATH . 'Flames/Kernel/Client/Engine/Flames/Kernel/web.wasm', 'r');

        while(!feof($fileStream)) {
            $buffer = fread($fileStream, 1024000); // 1 mb
            echo $buffer;
            ob_flush();
            flush();
        }

        return true;
    }
}