<?php

namespace Flames\Browser;

use App\Client\Component\Loading;
use Flames\Header;
use Flames\Js;
use Flames\Kernel;
use Flames\Http;

class Page
{
    public static function load(string $uri, $delegate = null): mixed
    {
        if (Kernel::MODULE === 'SERVER') {
            return Header::redirect($uri);
        }

        $client = new Http\Client();
        $request = new Http\Async\Request('GET', $uri, [  'Content-Type' => 'application/json']);
        $client->sendAsync($request)->then(function(Http\Async\Response $response) use ($uri, $delegate) {
            $data = $response->getBody();
            if ($response->getStatusCode() !== 200) {
                return Header::redirect($uri);
            }

            self::processPage($uri, $data, $delegate);
            return null;
        });

        return null;
    }

    protected static function processPage($uri, $html, $delegate = null)
    {
        Js::eval("window.history.pushState('change', 'Title', '" . $uri . "');");
        $head = null;
        $headPos = strpos($html, '<head');
        if ($headPos !== false) {
            $_head = substr($html, $headPos);
            $headClosePos = strpos($_head, '</head>');
            if ($headClosePos !== false) {
                $head = substr($_head, 0, $headClosePos + 7);
            }
        }

        $body = null;
        $bodyClasses = null;
        $bodyPos = strpos($html, '<body');
        if ($bodyPos !== false) {
            $_body = substr($html, $bodyPos);
            $bodyClosePos = strpos($_body, '</body>');
            if ($bodyClosePos !== false) {
                $_body = substr($_body, 0, $bodyClosePos);

                $bodyHeadClosePos = strpos($_body, '>');
                if ($bodyHeadClosePos !== null) {
                    $_bodyHead = substr($_body, 0, $bodyHeadClosePos + 1);
                    $body = substr($_body, $bodyHeadClosePos + 1);

                    $classPos = strpos($_bodyHead, 'class="');
                    if ($classPos !== null) {
                        $_class = substr($_bodyHead, $classPos + 7);
                        $classClosePos = strpos($_class, '"');
                        if ($classClosePos !== null) {
                            $bodyClasses = substr($_class, 0, $classClosePos);
                        }
                    }
                }
            }
        }

        $currentBody = Js::getWindow()->document->body->innerHTML;
        preg_match_all('#<script(.*?)<\/script>#is', $body, $matches);
        $scripts = $matches[0];
        foreach ($scripts as $script) {
            if (str_contains($currentBody, $script) === true) {
                $body = str_replace($script, '', $body);
            }
        }

        $flamesData = null;
        $flamesPos = strpos($body, '<flames hidden>');
        if ($flamesPos !== false) {
            $_flamesData = substr($body, $flamesPos + 15);
            $body = substr($body, 0, $flamesPos);
            $flamesClosePos = strpos($_flamesData, '<');
            if ($flamesClosePos !== false) {
                $flamesData = substr($_flamesData, 0, $flamesClosePos);
            }
        }

        self::swapHtml($head, $body);
        self::dispatchEvents($uri, $flamesData, $delegate);
    }

    protected static function swapHtml($head, $body)
    {
        // TODO: change header styles
//        Js::eval("
//            var head = document.querySelector('head');
//            if (head !== null) {
//                var removeCount = 0;
//                do {
//                    removeCount = 0;
//                    for (var child of head.children) {
//                        if (!(child.tagName === 'STYLE' || child.tagName === 'LINK')) {
//                            child.remove();
//                            removeCount++;
//                        }
//                    }
//                } while (removeCount > 0);
//            }
//        ");

        Js::eval("
            var body = document.querySelector('body');
            if (body !== null) {
                var removeCount = 0;
                do {
                    removeCount = 0;
                    for (var child of body.children) {
                        if (!(child.tagName === 'FLAMES' || child.tagName === 'SCRIPT')) {
                            child.remove();
                            removeCount++;
                        }
                    }
                } while (removeCount > 0);
            }
        ");

//        Js::eval("
//            var html = document.querySelector('html');
//            html.insertAdjacentHTML('afterbegin', decodeURIComponent('" . rawurlencode($head) . "'))
//        ");
//
        Js::eval("
            var body = document.querySelector('body');
            body.insertAdjacentHTML('afterbegin', decodeURIComponent('" . rawurlencode($body) . "'))
        ");
    }

    protected static function dispatchEvents($uri, $flamesData, $delegate = null)
    {
        if ($flamesData !== null) {
            Kernel::__injectData($flamesData);
        }

        Kernel::__loader();
    }
}