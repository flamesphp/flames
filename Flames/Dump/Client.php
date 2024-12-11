<?php

namespace {
    function dump() : void
    {
        $debug = debug_backtrace();
        $class = $debug[1]['class'];
        $line = $debug[0]['line'];

        $window = \Flames\Js::getWindow();
        $classPath = $class;
        if (str_starts_with($classPath, 'Flames\\') === true && $window->Flames->Internal->composer === true) {
            $classPath = ('vendor\\flamesphp\\flames\\' . $class);
        }

        $link = 'phpstorm://open?file={DUMP_LOCAL_PATH}\\';
        $link .= ($classPath . '.php&line=' . $line);

        $arg = func_get_args()[0];


        if ($arg instanceof \Flames\Element) {
            $uid = $arg->getAttribute($window->Flames->Internal->char . 'uid');
            if ($uid === null) {
                $window->Flames->Internal->uid = ($window->Flames->Internal->uid + 1);
                $uid = $window->Flames->Internal->generateUid($window->Flames->Internal->uid);
                $arg->setAttribute($window->Flames->Internal->char . 'uid', $uid);
            }

            $window->Flames->Internal->dump(
                $window->document->querySelector('[' . $window->Flames->Internal->char . 'uid="' . $uid. '"]'),
                (' | Called at ' .  $class . ':' . $line . ' | ' . $link)
            );

            $arg->removeAttribute($window->Flames->Internal->char . 'uid');
            return;
        }

        if (is_object($arg) === true) {
            $arg = (array)$arg;
        }

        $data = base64_encode(json_encode($arg));
        $window->Flames->Internal->dump(
            $window->JSON->parse($window->atob($data)),
            (' | Called at ' . $class . ':' . $line . ' | ' . $link)
        );
    }

    function dd() : void
    {
        $debug = debug_backtrace();
        $class = $debug[1]['class'];
        $line = $debug[0]['line'];

        $window = \Flames\Js::getWindow();
        $classPath = $class;
        if ($window->Flames->Internal->composer === true) {
            $classPath = ('COMPOSER-' . $class);
        }

        $link = 'phpstorm://open?file={DUMP_LOCAL_PATH}\\';
        $link .= ($classPath . '.php&line=' . $line);

        $arg = func_get_args()[0];

        if ($arg instanceof \Flames\Element) {
            $window->Flames->Internal->dump(
                $window->document->querySelector('[' . $window->Flames->Internal->char . 'uid="' . $arg->uid. '"]'),
                (' | Called at ' . $class . ':' . $line . ' | ' . $link)
            );
            exit;
        }

        if (is_object($arg) === true) {
            $arg = (array)$arg;
        }

        $data = base64_encode(json_encode($arg));
        $window->Flames->Internal->dump(
            $window->JSON->parse($window->atob($data)),
            (' | Called at ' . str_replace('\\', '\\\\', $class . ':' . $line . ' | ' . $link))
        );
        exit;
    }
}
