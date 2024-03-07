<?php

namespace App\Server\Repository;

use Flames\Repository;
use Flames\ORM;
use \App\Server\Model\User as UserModel;

#[ORM\Database(name: 'mysql')]
#[ORM\Model(model: UserModel::class)]
class User extends Repository
{
    public static function get(mixed $index) : UserModel|null
    {
        return parent::get($index);
    }

    public static function getByEmail(string $email) : UserModel|null
    {
        return parent::withFilters([
            ['email' => $email]
        ]);
    }

}