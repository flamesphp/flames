<?php

namespace Flames\Kernel;

use Flames\Collection\Arr;
use Flames\Connection;
use Flames\RequestData;
use Flames\Required;

/**
 * @internal
 */
class Route
{
    public static function mountRequestData(Arr $routeData) : RequestData
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $headers = getallheaders();
        $contentType = null;
        if (isset($headers['Content-Type']) === true) {
            $contentType = $headers['Content-Type'];
        } elseif (isset($headers['content-type']) === true) {
            $contentType = $headers['content-type'];
        }

        // Request Global Data
        $request = [];

        // Get Query String Data
        $queryString = null;
        $splitUri = explode('?', $_SERVER['REQUEST_URI']);
        if (count($splitUri) >= 2) {
            $queryString = [];
            parse_str($splitUri[1], $queryString);
            foreach ($queryString as $key => $value) {
                $request[$key] = $value;
            }
        }

        // Get Multipart Data
        $multipart = [];
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
        $urlEncoded = [];
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
        $json = null;
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
            $_SERVER['QUERY_STRING'],
            Arr($queryString),
            $routeData->parameters,
            Arr($multipart),
            Arr($urlEncoded),
            $json,
            Arr($request),
            Arr($headers),
            $_SERVER['SERVER_NAME'],
            $_SERVER['SERVER_PORT'],
            Connection::getIp(),
        );
    }
}