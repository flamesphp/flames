<?php

namespace App\Server\Controller;

use Flames\Controller;
use Flames\Controller\Response;
use Flames\Cryptography\Password;
use Flames\RequestData;
use App\Server\Model\User;
use App\Server\Repository\User as UserRepository;

class Test extends Controller
{
    public function onRequest(RequestData $requestData) : Response|string
    {
//        dump($requestData);

         $user = UserRepository::get(1);
        dump($user);

        exit;

//        $user = new User();///['name' => 'John B.asdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsadasdsad']);
//        $user->save();
//
//        $user->name = 'Kazz';
//        $user->email = 'kazzxd1@gmail.com';
//        dump($user);
//        $user->save();
//
//        dump($user->getChanged());
//        $user->save(); // nothing to update
//
//        $user->email = 'kazzkzpk@gmail.com';
//        $user->password = Password::toHash('test', $user->id);
//        dump($user->getChanged(false));
//        $user->save();
//
//        dump($user);




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