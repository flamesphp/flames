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
        dump($uri);
        exit;

        return false;
    }

    protected static function dispatchFlames() : bool
    {
        header('Content-Type: application/javascript; charset=utf-8');
        $fileStream = fopen(KERNEL_PATH . '.client/.native/flames.js', 'r');

        while(!feof($fileStream)) {
            $buffer = fgets($fileStream, 128000); // 128 kb
            echo $buffer;
            ob_flush();
            flush();
        }

        return true;
    }

    protected static function dispatchKernel() : bool
    {
        header('Content-Type: application/javascript; charset=utf-8');
        $fileStream = fopen(KERNEL_PATH . '.client/.native/flames/kernel.mjs', 'r');

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
        $fileStream = fopen(KERNEL_PATH . '.client/.native/flames/kernel/base.mjs', 'r');

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
        $fileStream = fopen(KERNEL_PATH . '.client/.native/flames/kernel/web.mjs', 'r');

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
        $fileStream = fopen(KERNEL_PATH . '.client/.native/flames/kernel/web.wasm', 'r');

        while(!feof($fileStream)) {
            $buffer = fread($fileStream, 1024000); // 1 mb
            echo $buffer;
            ob_flush();
            flush();
        }

        return true;
    }
}