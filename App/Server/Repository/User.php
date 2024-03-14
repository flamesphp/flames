<?php

namespace App\Server\Repository;

use App\Server\Model\User as UserModel;
use Flames\Collection\Arr;
use Flames\ORM;
use Flames\Repository;

#[ORM\Database(name: 'mysql')]
#[ORM\Model(model: UserModel::class)]
class User extends Repository
{
    public static function get(mixed $index) : UserModel|null
    {
        return parent::get($index);
    }

    public static function getByEmail(string $email) : Arr
    {
        $repository = new self();

        return parent::withFilters([
            'email' => $email
        ]);
    }

}