<?php

namespace App\Server\Controller;

use Flames\Controller;
use Flames\Controller\Response;
use Flames\RequestData;
use App\Server\Model\User;

class Test extends Controller
{
    public function onRequest(RequestData $requestData) : Response|string
    {
        dump($requestData);

        $user = new User();
        dump($user);

//        new Iansoins();

        exit;

        $data = Arr();
        $data->test = 123;
        $data->text = 'Olá, tudo bem?';

        return $this->success($data);
//        echo  file_get_contents('test.html');
//        exit;
    }
}