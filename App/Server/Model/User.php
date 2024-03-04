<?php

namespace App\Server\Model;

use Flames\Collection\Arr;
use Flames\Model;
use Flames\ORM;
use JetBrains\PhpStorm\NoReturn;

/**
 * Description for the class
 * @property id $id
 * @property string|null $name
 * @property string|null $dummy
 */

#[ORM\Database(name: 'mysql')]
#[ORM\Table(name: 'user')]
class User extends Model
{
    #[ORM\Column(name: 'id', type: 'bigint', primary: true, autoIncrement: true, index: true, unique: true)]
    protected int $id;

    #[ORM\Column(name: 'name', type: 'varchar', length: 256, nullable: true)]
    protected string|null $name = null;

    #[ORM\Column(name: 'dummy', type: 'varchar', length: 256, default: 'dummy')]
    protected string|null $dummy = 'dummy';
}