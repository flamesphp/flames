<?php

class File
{
    /**
     * Checks if a file or directory exists.
     *
     * @param string $path The path to the file or directory.
     * @return bool Returns TRUE if the file or directory exists, FALSE otherwise.
     */
    public static function exists(string $path) : bool
    {
        return file_exists($path);
    }

    /**
     * Retrieves the MIME type of a file based on its path.
     *
     * @param string $path The path to the file.
     * @return string|null The MIME type of the file, or null if file not exists.
     */
    public static function getMimeType(string $path) : string|null
    {
        try  {
            return mime_content_type($path);
        } catch (\Error $e) {
            return null;
        }
    }
}