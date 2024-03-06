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
//        dump($requestData);

        $user = new User(['name' => 'John B.']);
//        $user->id = 1;
//        dump($user->name);
//        $user->name = 'Kazz';
//        dump($user->name);
        dump($user);
//        $user->save();





        exit;

        $hash = Password::toHash('test');
        dump($hash);
        dump(Password::isValidHash($hash, 'test'));

        exit;

        $data = Arr();
        $data->test = 123;
        $data->text = 'Dummy text';

        return $this->success($data);
    }
}