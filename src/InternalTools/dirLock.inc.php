<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools;

use Error;
use const DIRECTORY_SEPARATOR as DS;

function dirLock(String $directory){
    assert(isNormalizedPath($directory));

    $lockFilePath = $directory . DS . "opal.lock";
    $locked = retryWithinTimeLimit(function() use(&$lockHandle, &$lockFilePath){
        $lockHandle = @fopen($lockFilePath, "c");
        if($lockHandle === FALSE) return FALSE;
        $locked = flock($lockHandle, LOCK_EX | LOCK_NB);
        return $locked;
    }, 30.0, 0.0);

    if(!$locked) throw new Error("Unable to lock the directory within 30 seconds");

    $actualContents = glob($directory . DS . "*");
    $isDirectoryNotEmpty = $actualContents !== [$lockFilePath];
    if($isDirectoryNotEmpty){
        fclose($lockHandle);
        throw new Error("The directory $directory is not empty");
    }

    return $lockHandle;
}
