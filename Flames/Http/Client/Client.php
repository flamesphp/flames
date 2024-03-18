<?php

namespace Flames\Http\Client;

use Exception;
use Flames\Collection\Arr;
use Flames\JS;
use Flames\Http\Async\Request;
use Flames\Http\Async\Response;

/**
 * @internal
 */
class Client
{
    protected static $lastId = 0;

    protected static $callbacks = [];

    protected string|null $baseUri = null;
    protected Request|null $request = null;

    public function __construct(Arr|array $options = [])
    {
        $options = (array)$options;

        if (isset($options['base_uri']) === true) {
            $this->baseUri = $options['base_uri'];
        }
    }

    public function sendAsync(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    public function then($delegate)
    {
        self::$lastId += 1;
        self::$callbacks[self::$lastId] = $delegate;

        $data = [
            'id'      => self::$lastId,
            'method'  => strtolower($this->request->getMethod()),
            'url'     => $this->request->getUri(),
            'header'  => $this->request->getHeaders()
        ];

        if ($this->baseUri !== null) {
            $data['url'] = ($this->baseUri . $data['url']);
        }

        JS::eval('Flames.Internal.Http(\'' . json_encode($data) . '\');');
    }


    public function send($request, array $options = [])
    {
        throw new Exception('Function unsupported on client side, use request.');
    }

    public function sendRequest($request)
    {
        throw new Exception('Function unsupported on client side, use request.');
    }

    public function requestAsync(string $method, $uri = '', array $options = [])
    {
        throw new Exception('Function unsupported on client side, use request.');
    }

    public function request(string $method, $uri = '', Arr|array|null $options = [], $delegate = null)
    {
        throw new Exception('Function unsupported on client side, use request.');
    }

    public function getConfig(string $option = null)
    {
        throw new Exception('Function unsupported on client side, use request.');
    }

    public static function callback(int $id, string $data)
    {
        $delegate = self::$callbacks[$id];

        $data = json_decode($data);

        $body = null;
        $code = 200;
        $headers = null;
        if ($data->status === 'error') {
            $code = 500;
            $body = $data->message;
        } else {
            $code = $data->status;
            if (is_object($data->body) === true || is_array($data->body) === true) {
                $data->body = json_encode($data->body);
            }
            $body = $data->body;
        }

        $_header = Arr();
        foreach ($data->header as $header) {
            $_header[$header[0]] = $header[1];
        }

        $response = new Response($code, $body, $_header);
        $delegate($response);
    }
}