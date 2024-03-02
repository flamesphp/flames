<?php

namespace Flames;

use Flames\Collection\Arr;
use Flames\Controller\Response;
use Flames\RequestData;

abstract class Controller
{
    public function onRequest(RequestData $requestData) : Response|string
    {
        return $this->success();
    }

    public function success(Arr|array|string $data = null, int $code = 200, Arr|array|null $headers = null) : Response|string
    {
        if (is_string($data)) {

        } else {
            if (is_array($data) === true) {
                $data = Arr($data);
            }

            return new Response(null, $data, $code, $headers);
        }

        return '';
    }

    public function error(Arr|array|string $data = null, int $code = 200, Arr|array|null $headers = null) : Response|string
    {
        return $this->success($data, 500, $headers);
    }
}