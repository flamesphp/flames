<?php

namespace App\Server\Controller;

use App\Server\Model\User;
use Flames\Controller;
use Flames\Controller\Response;
use Flames\Cryptography\Password;
use Flames\RequestData;

class Test extends Controller
{

    public function onRequest(RequestData $requestData) : Response|string
    {
//        $user = new User();///['name' => 'John B.']);
//        $user->name = 'Gunnar';
//        $user->email = 'gunnar@gmail.com';
//
//        $user->save();
//        dump($user);
//        exit;



//        dump($requestData);

//        $user = UserRepository::getByEmail('kazzxd1@gmail.com');
//        dump($user);

//        $users = UserRepository::withFilters( [
//                'name'  =>   'Kazz',
//                ['id', '<=', '100'],
//            ], [
//                'order' => [
//                    'id' => 'ASC'
//                ],
//                'limit' => '1',
//                'offset' => '1'
//            ]
//        );
//        dump($users);
//        exit;

//        $user = UserRepository::get(99);
//        dump($user);
//        $user->password = Password::toHash('test', $user->id);
//        $user->save();
//        dump($user);
//        exit;

        $user = new User();///['name' => 'John B.']);

//
        $user->name = 'Kazz';
        $user->email = 'kazzxd4@gmail.com';
        dump($user);
        $user->save();
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