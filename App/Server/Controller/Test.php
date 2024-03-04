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

        $user = new User(['name' => 'John B.']);
        dump($user->name);
        dump($user->id);
        $user->test = 123;
        $user->name = 'Kazz';
        dump($user->test);
        dump($user->name);

        dump($user);

        exit;

        $data = Arr();
        $data->test = 123;
        $data->text = 'Olá, tudo bem?';

        return $this->success($data);
//        echo  file_get_contents('test.html');
//        exit;
    }
}