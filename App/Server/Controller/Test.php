<?php

namespace App\Server\Controller;

use Flames\Controller;
use Flames\Controller\Response;
use Flames\Cryptography\Password;
use Flames\RequestData;
use App\Server\Model\User;

class Test extends Controller
{
    public function onRequest(RequestData $requestData) : Response|string
    {
        dump($requestData);


        $hash = Password::toHash('test');
        dump($hash);

        dump(Password::isValidHash($hash, 'test'));
        exit;


        $user = new User(['name' => 'John B.']);
        dump($user->name);
        $user->name = 'Kazz';
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