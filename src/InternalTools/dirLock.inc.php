<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools;

use const DIRECTORY_SEPARATOR as DS;

class LockTimeout{}
class NonEmptyDirectory{}

function dirLock(
    String $directory,
    ?Int $directoryPermissions,
    Int $timeout = 30
){
    assert(isNormalizedPath($directory));

    $directoryPermissions = $directoryPermissions ?? 0755;

    $lockFilePath = $directory . DS . "opal.lock";
    $locked = retryWithinTimeLimit(function() use(
        &$lockHandle, &$lockFilePath, &$directory, &$directoryPermissions
    ){
        @mkdir($directory, $directoryPermissions, TRUE);
        $lockHandle = @fopen($lockFilePath, "c");
        if($lockHandle === FALSE) return FALSE;
        $locked = flock($lockHandle, LOCK_EX | LOCK_NB);
        return $locked;
    }, (Float)$timeout, 0.0);

    if(!$locked) return new LockTimeout();

    /** @var Resource $lockHandle */

    $actualContents = glob($directory . DS . "*");
    $isDirectoryNotEmpty = $actualContents !== [$lockFilePath];
    if($isDirectoryNotEmpty){
        fclose($lockHandle);
        return new NonEmptyDirectory();
    }

    return $lockHandle;
}
