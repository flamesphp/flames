<?php

namespace Flames;

class PHP
{
    public static function eval(string $code) : mixed
    {
        return eval($code);
    }
}