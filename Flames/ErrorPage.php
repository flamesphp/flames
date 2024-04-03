<?php

namespace Flames;

use Flames\CLI\Command\Build\Project\StaticEx;
use Flames\Collection\Arr;
use Flames\Controller\Response;
use Flames\Kernel\Route;

final class ErrorPage
{
    public static function dispatch404()
    {
        Header::clear();

        $data = self::route404();
        if ($data !== false) {
            Header::set('code', 404);
            Header::send();
            echo $data;
            return;
        }

        Header::set('code', 404);
        Header::send();
    }

    public static function dispatch500()
    {
        Header::clear();
        Header::set('code', 500);
        Header::send();
    }

    protected static function route404() : string|bool
    {
        $router = Kernel::getDefaultRouter();
        if ($router === null) {
            return false;
        }

        $metadatas = $router->getMetadata();
        foreach ($metadatas as $metadata) {
            if ($metadata->routeFormatted === '404') {
                $currentUri = $_SERVER['REQUEST_URI'];
                $_SERVER['REQUEST_URI'] = '404';
                $match = $router->getMatch();
                $_SERVER['REQUEST_URI'] = $currentUri;

                $responseData = StaticEx::getResponse($match);
                return $responseData->output;
            }
        }

        return false;
    }
}