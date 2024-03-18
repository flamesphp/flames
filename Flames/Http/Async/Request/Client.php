<?php

namespace Flames\Http\Async\Request;

use Flames\Collection\Arr;

/**
 * @internal
 */
class Client
{
    private string $method;
    private string $url;

    public function __construct(string $method = 'GET', string $url = '/')
    {
        $this->method = strtoupper($method);
        $this->url = $url;
    }

    public function getMethod() : string
    {
        return $this->method;
    }

    public function getUri() : string
    {
        return $this->url;
    }
}