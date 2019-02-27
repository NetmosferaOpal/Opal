<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools;

use const DIRECTORY_SEPARATOR as DS;
use function umask;

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
        &$lock, &$lockFilePath, &$directory, &$directoryPermissions
    ){
        $saveUMask = umask(0);
        @mkdir($directory, $directoryPermissions, TRUE);
        $lock = @fopen($lockFilePath, "c");
        umask($saveUMask);

        if($lock === FALSE){
            return FALSE;
        }

        return $locked = flock($lock, LOCK_EX | LOCK_NB);
    }, (Float)$timeout, 0.0);

    if(!$locked) return new LockTimeout();

    /** @var Resource $lock */

    $actualContents = glob($directory . DS . "*");
    $isDirectoryNotEmpty = $actualContents !== [$lockFilePath];
    if($isDirectoryNotEmpty){
        fclose($lock);
        return new NonEmptyDirectory();
    }

    return $lock;
}
