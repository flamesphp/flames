<?php

namespace Flames\Kernel;

use Flames\CLI;
use Flames\Collection\Arr;
use Flames\Connection;
use Flames\JS;
use Flames\RequestData;
use Flames\Required;

/**
 * @internal
 */
class Route
{
    public static function mountRequestData(Arr $routeData, string|null $ip = null) : RequestData
    {
        $isCLI       = (CLI::isCLI() === true);
        $method      = null;
        $headers     = null;
        $contentType = null;
        $multipart   = [];
        $request     = [];
        $urlEncoded  = [];
        $json        = null;
        $queryString = null;

        if (\Flames\Kernel::MODULE === 'SERVER')
        {
            if ($isCLI === false) {
                $method = $_SERVER['REQUEST_METHOD'];
                $headers = (function_exists('getallheaders') ? getallheaders() : null);
                $contentType = null;
                if (isset($headers['Content-Type']) === true) {
                    $contentType = $headers['Content-Type'];
                } elseif (isset($headers['content-type']) === true) {
                    $contentType = $headers['content-type'];
                }

                // Get Query String Data
                $splitUri = explode('?', $_SERVER['REQUEST_URI']);
                if (count($splitUri) >= 2) {
                    $queryString = [];
                    parse_str($splitUri[1], $queryString);
                    foreach ($queryString as $key => $value) {
                        $request[$key] = $value;
                    }
                }

                // Get Multipart Data
                if ($contentType !== null && str_starts_with($contentType, 'multipart/form-data')) {
                    if ($method === 'GET') {
                        Required::_function('parse_raw_http_request');
                        parse_raw_http_request($multipart);
                    } else {
                        foreach ($_POST as $key => $value) {
                            $multipart[$key] = $value;
                        }
                    }
                    foreach ($multipart as $key => $value) {
                        $request[$key] = $value;
                    }
                }

                // Get Form UrlEncoded Data
                if ($contentType !== null && str_starts_with($contentType, 'application/x-www-form-urlencoded')) {
                    if ($method === 'GET') {
                        $input = file_get_contents('php://input');
                        parse_str($input, $urlEncoded);
                    } else {
                        foreach ($_POST as $key => $value) {
                            $urlEncoded[$key] = $value;
                        }
                    }

                    foreach ($_POST as $key => $value) {
                        $request[$key] = $value;
                    }
                }

                // Update non-get requests PHP default
                if ($method !== 'GET') {
                    foreach ($_POST as $key => $value) {
                        $request[$key] = $value;
                    }
                }

                // Get JSON Data
                if ($contentType !== null && str_starts_with($contentType, 'application/json')) {
                    $json = @json_decode(file_get_contents('php://input'));
                    if ($json !== false) {
                        $json = (array)$json;
                        foreach ($json as $key => $value) {
                            $request[$key] = $value;
                        }
                        $json = Arr($json);
                    } else {
                        $json = null;
                    }
                }

                // Uries Internal Route Data
                foreach ($routeData->parameters as $key => $value) {
                    $request[$key] = $value;
                }

                return new RequestData(
                    $method,
                    explode('?', $_SERVER['REQUEST_URI'])[0],
                    Arr($queryString),
                    $routeData->parameters,
                    Arr($multipart),
                    Arr($urlEncoded),
                    $json,
                    Arr($request),
                    Arr($headers),
                    $_SERVER['SERVER_NAME'],
                    $_SERVER['SERVER_PORT'],
                    $ip,
                    null,
                    null
                );
            }

            $method = 'CLI';

            return new RequestData(
                'CLI',
                null,
                Arr($queryString),
                $routeData->parameters,
                Arr($multipart),
                Arr($urlEncoded),
                $json,
                Arr($request),
                Arr($headers),
                null,
                null,
                null,
                $routeData->command,
                null
            );
        }

        // Get Query String Data
        $splitUri = explode('?', $_SERVER['REQUEST_URI']);
        if (count($splitUri) >= 2) {
            $queryString = [];
            parse_str($splitUri[1], $queryString);
            foreach ($queryString as $key => $value) {
                $request[$key] = $value;
            }
        }

        // Uries Internal Route Data
        foreach ($routeData->parameters as $key => $value) {
            $request[$key] = $value;
        }

        return new RequestData(
            'GET',
            explode('?', $_SERVER['REQUEST_URI'])[0],
            Arr($queryString),
            $routeData->parameters,
            Arr(),
            Arr(),
            $json,
            Arr($request),
            Arr(),
            null,
            null,
            null,
            null,
            Arr(\Flames\Kernel::__getData())
        );
    }
}