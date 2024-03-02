<?php

namespace App\Server\Controller;

use Flames\Controller\Response;
use Flames\RequestData;

class Test extends \Flames\Controller
{
    public function onRequest(RequestData $requestData) : Response|string
    {
        dump($requestData);
        exit;

        $data = Arr();
        $data->test = 123;
        $data->text = 'Olá, tudo bem?';

        return $this->success($data);
//        echo  file_get_contents('test.html');
//        exit;
    }
}