<?php

namespace Flames;

use Flames\Collection\Arr;

/**
 * Description for the class
 * @property string $method
 * @property string $url
 * @property string $controller
 * @property Arr $request
 * @property Arr $uries
 * @property Arr $queries
 * @property Arr $multipart
 * @property Arr $urlEncoded
 * @property Arr|null $json
 * @property string $host
 * @property int $port
 * @property string $ip
 * @property Arr $headers
 */
class RequestData
{
    protected string $method;
    protected string $url;
    protected Arr $request;
    protected Arr $uries;
    protected Arr $queries;
    protected Arr $multipart;
    protected Arr $urlEncoded;
    protected Arr|null $json;
    protected string $host;
    protected int $port;
    protected string $ip;
    protected Arr $headers;

    public function __construct(string $method, string $url, Arr $queries, Arr $uries, Arr $multipart, Arr $urlEncoded, Arr|null $json, Arr $request, Arr $headers, string $host, string $port, string|null $ip)
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
        $this->request     = $request;

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
        }
    }
}