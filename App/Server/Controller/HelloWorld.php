<?php

namespace App\Server\Controller;

use Flames\Controller\Response;
use Flames\RequestData;

class HelloWorld extends \Flames\Controller
{
    public function onRequest(RequestData $requestData) : Response|string
    {
        return parent::onRequest($requestData);
    }
}