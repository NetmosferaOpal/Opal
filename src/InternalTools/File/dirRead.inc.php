<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools\File;

use Closure;
use const DIRECTORY_SEPARATOR as DS;
use function opendir;
use function readdir;
use function closedir;

function dirRead(
    String $directory,
    ?Closure $afterOpen = NULL,
    ?Closure $afterRead = NULL
){
    $directory = rtrim($directory, "/\\");
    $handle = opendir($directory);
    if($afterOpen !== NULL) $afterOpen($handle !== FALSE);
    if($handle === FALSE) return NULL;
    try{
        $fileNames = [];
        fetch:
        $fileName = readdir($handle);
        if($fileName === "." || $fileName === "..") goto fetch;
        if($fileName === FALSE){
            if($afterRead !== NULL) $afterRead(NULL);
            return $fileNames;
        }
        $path = $directory . DS . $fileName;
        if($afterRead !== NULL) $afterRead($path);
        $fileNames[] = $path;
        goto fetch;
    }finally{
        closedir($handle);
    }
}
