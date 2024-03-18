<?php

namespace Flames\Kernel\Client\Build;

use Flames\Collection\Arr;

/**
 * @internal
 */
class Data
{
    private const __VERSION__ = 2;

    public static function mountData(string $class) : Arr
    {
        $path = (ROOT_PATH . str_replace('\\', '/', $class) . '.php');

        $basePath  = (ROOT_PATH . '.cache/client-controller/');
        $cachePath = ($basePath . sha1($class));
        $currentTime = filemtime($path);

        if (file_exists($cachePath) === true && filemtime($cachePath) === $currentTime) {
            $data = unserialize(file_get_contents($cachePath));
            if ($data->version === self::__VERSION__) {
                return $data;
            }
        }

        $data = self::__getReflection($class);
        $success = @file_put_contents($cachePath, serialize($data));
        if ($success === false) {
            if (is_dir($basePath) === false) {
                mkdir($basePath, 0777, true);
                @file_put_contents($cachePath, serialize($data));
            }
        }
        @touch($cachePath, $currentTime);
        return $data;
    }

    private static function __getReflection(string $class) : Arr
    {
        $data = Arr([
            'version'  => self::__VERSION__,
            'class'    => $class,
            'methods'  => Arr()
        ]);

        $reflection = new \ReflectionClass($class);

        $methods = $reflection->getMethods();
        foreach ($methods as $method) {
            if ($method->name === 'success' || $method->name === 'error' || $method->name === '__constructStatic') {
                continue;
            }

            $attributes = $method->getAttributes();
            foreach($attributes as $attribute) {
                $attributeName = $attribute->getName();

                if ($attributeName === \Flames\Browser\Click::class) {
                    $arguments = $attribute->getArguments();
                    if (isset($arguments['uid'])) {
                        $data->methods[$method->name] = Arr([
                            'name' => $method->name,
                            'uid' => $arguments['uid'],
                            'type' => 'click'
                        ]);
                    }
                }
            }
        }

        return $data;
    }
}
