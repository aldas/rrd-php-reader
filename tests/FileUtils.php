<?php

namespace Tests;


final class FileUtils
{
    public static function getContents(string $file): string 
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR .'data'. DIRECTORY_SEPARATOR . $file;
        return file_get_contents($path);
    }

}