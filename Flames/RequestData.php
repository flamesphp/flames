<?php

namespace Flames;

use Flames\Collection\Arr;

/**
 * Description for the class
 * @property string $method
 * @property string|null $url
 * @property string $controller
 * @property Arr $request
 * @property Arr $uries
 * @property Arr $queries
 * @property Arr $multipart
 * @property Arr $urlEncoded
 * @property Arr|null $json
 * @property string|null $host
 * @property int|null $port
 * @property string|null $ip
 * @property string|null $command
 * @property Arr $headers
 * @property Arr $data
 */
class RequestData
{
    protected string $method;
    protected string|null $url;
    protected string|null $command;
    protected Arr $request;
    protected Arr $uries;
    protected Arr $queries;
    protected Arr $multipart;
    protected Arr $urlEncoded;
    protected Arr|null $json;
    protected string|null $host;
    protected int|null $port;
    protected string|null $ip;
    protected Arr|null $headers;
    protected Arr|null $data;

    public function __construct(string $method, string|null $url, Arr $queries, Arr $uries, Arr $multipart, Arr $urlEncoded, Arr|null $json, Arr $request, Arr $headers, string|null $host, string|null $port, string|null $ip, string|null $command, Arr|null $data)
    {
        $this->method     = $method;
        $this->url        = $url;
        $this->uries      = $uries;
        $this->queries    = $queries;
        $this->headers    = $headers;
        $this->host       = $host;
        $this->port       = (int)$port;
        $this->ip         = $ip;
        $this->multipart  = $multipart;
        $this->urlEncoded = $urlEncoded;
        $this->json       = $json;
        $this->request    = $request;
        $this->command    = $command;
        $this->data       = $data;

        return null;
    }

    public function __get(string $key) : mixed
    {
        $key = strtolower((string)$key);

        if ($key === 'method') {
            return $this->method;
        } elseif ($key === 'url') {
            return $this->url;
        } elseif ($key === 'controller') {
            return $this->controller;
        } elseif ($key === 'uries') {
            return $this->uries;
        } elseif ($key === 'queries') {
            return $this->queries;
        } elseif ($key === 'headers') {
            return $this->headers;
        } elseif ($key === 'host') {
            return $this->host;
        } elseif ($key === 'port') {
            return $this->port;
        } elseif ($key === 'ip') {
            return $this->ip;
        } elseif ($key === 'multipart') {
            return $this->multipart;
        } elseif ($key === 'urlencoded') {
            return $this->urlEncoded;
        } elseif ($key === 'json') {
            return $this->json;
        } elseif ($key === 'request') {
            return $this->request;
        } elseif ($key === 'command') {
            return $this->command;
        } elseif ($key === 'data') {
            return $this->data;
        }
    }
}