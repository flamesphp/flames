<?php

function dump() : void
{
    var_dump(func_get_args());
}

function dd() : void
{
    var_dump(func_get_args());
    exit;
}