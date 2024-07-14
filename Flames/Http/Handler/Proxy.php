<?php

namespace Flames\Http\Handler;

use Flames\Http\Promise\PromiseInterface;
use Flames\Http\Psr\Http\Message\RequestInterface;
use Flames\Http\RequestOptions;

/**
 * Provides basic proxies for handlers.
 *
 * @final
 */
class Proxy
{
    /**
     * Sends synchronous requests to a specific handler while sending all other
     * requests to another handler.
     *
     * @param callable(\Flames\Http\Psr\Http\Message\RequestInterface, array): \Flames\Http\Promise\PromiseInterface $default Handler used for normal responses
     * @param callable(\Flames\Http\Psr\Http\Message\RequestInterface, array): \Flames\Http\Promise\PromiseInterface $sync    Handler used for synchronous responses.
     *
     * @return callable(\Flames\Http\Psr\Http\Message\RequestInterface, array): \Flames\Http\Promise\PromiseInterface Returns the composed handler.
     */
    public static function wrapSync(callable $default, callable $sync): callable
    {
        return static function (RequestInterface $request, array $options) use ($default, $sync): PromiseInterface {
            return empty($options[RequestOptions::SYNCHRONOUS]) ? $default($request, $options) : $sync($request, $options);
        };
    }

    /**
     * Sends streaming requests to a streaming compatible handler while sending
     * all other requests to a default handler.
     *
     * This, for example, could be useful for taking advantage of the
     * performance benefits of curl while still supporting true streaming
     * through the StreamHandler.
     *
     * @param callable(\Flames\Http\Psr\Http\Message\RequestInterface, array): \Flames\Http\Promise\PromiseInterface $default   Handler used for non-streaming responses
     * @param callable(\Flames\Http\Psr\Http\Message\RequestInterface, array): \Flames\Http\Promise\PromiseInterface $streaming Handler used for streaming responses
     *
     * @return callable(\Flames\Http\Psr\Http\Message\RequestInterface, array): \Flames\Http\Promise\PromiseInterface Returns the composed handler.
     */
    public static function wrapStreaming(callable $default, callable $streaming): callable
    {
        return static function (RequestInterface $request, array $options) use ($default, $streaming): PromiseInterface {
            return empty($options['stream']) ? $default($request, $options) : $streaming($request, $options);
        };
    }
}
