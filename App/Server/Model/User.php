<?php

namespace App\Server\Model;

use Flames\Collection\Arr;
use Flames\Model;
use Flames\ORM;
use JetBrains\PhpStorm\NoReturn;

/**
 * Description for the class
 * @property int $id
 * @property string|null $name
 * @property string|null $dummy
 */

#[ORM\Database(name: 'mysql')]
#[ORM\Table(name: 'user')]
class User extends Model
{
    #[ORM\Column(name: 'id', type: 'bigint', primary: true, autoIncrement: true)]
    protected int $id;

    #[ORM\Column(name: 'name', type: 'varchar', length: 256, nullable: true)]
    protected string|null $name = null;

    #[ORM\Column(name: 'email', type: 'varchar', length: 128, nullable: true)]
    protected string|null $email = null;

    #[ORM\Column(name: 'password', type: 'varchar', length: 128, nullable: true)]
    protected string|null $password = null;

//    #[ORM\Column(name: 'dummy', type: 'varchar', length: 256, default: 'dummy', index: true)]
//    protected string|null $dummy = 'dummy';

//    #[ORM\Column(name: 'dummy2', type: 'varchar', length: 256, default: 'dummy')]
//    protected string|null $dummy2 = 'dummy2';

//    #[ORM\Column(name: 'my_unique', type: 'varchar', length: 256, unique: true)]
//    protected string $myUnique;
}