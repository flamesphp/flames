<?php

class File
{
    public static function exists(string $path) : bool
    {
        return file_exists($path);
    }

    public static function getMimeType(string $path)
    {

    }
}