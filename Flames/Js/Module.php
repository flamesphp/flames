<?php

namespace Flames\Js;

use Flames\Js;

class Module
{
    protected static $hashs = [];

    protected $uri = null;
    protected $hash = null;
    protected $delegate = null;

    public function __construct($uri)
    {
        $this->uri = $uri;
        $this->hash = sha1($uri);

        self::$hashs[$this->hash] = $this;
    }

    public function then(\Closure $delegate)
    {
        $this->delegate = $delegate;

        $window = Js::getWindow();
        $module = $window->Flames->Internal->getModuleByHash($this->hash);
        if ($module !== null) {
            $delegate = $this->delegate;
            $delegate($module);
            return;
        }

        $window = Js::getWindow();
        $window->Flames->Internal->importModule($this->uri, $this->hash);
    }

    public static function import($uri): Module
    {
        return new Module($uri);
    }

    public static function onLoad($hash)
    {
        if (isset(self::$hashs[$hash]) === false) {
            return;
        }

        $window = Js::getWindow();
        $module = $window->Flames->Internal->getModuleByHash($hash);

        $instance = self::$hashs[$hash];

        $delegate = $instance->delegate;
        $delegate($module);
    }
}

