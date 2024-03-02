<?php

namespace App\Server\Model;

use Flames\Model;
use Flames\ORM;

#[ORM\Database(name: 'mysql')]
#[ORM\Table(name: 'user')]
class User extends Model
{
    protected $test;

    public function __construct()
    {
        dump(self::getDatabase());
    }
}