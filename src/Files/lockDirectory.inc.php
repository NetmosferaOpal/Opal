<?php declare(strict_types = 1);

namespace Netmosfera\Opal\Files;

use Error;
use Netmosfera\Opal\Path;
use const DIRECTORY_SEPARATOR as DS;
use function Netmosfera\Opal\Misc\retryWithinTimeLimit;
use function umask;

function lockDirectory(
    Path $path,
    ?Int $directoryPermissions,
    Int $timeout = 30
){
    $directoryPermissions = $directoryPermissions ?? 0755;

    $lockFilePath = $path->string . DS . "opal.lock";
    $locked = retryWithinTimeLimit(function() use(
        &$lock, &$lockFilePath, &$path, &$directoryPermissions
    ){
        $saveUMask = umask(0);
        @mkdir($path->string, $directoryPermissions, TRUE);
        $lock = @fopen($lockFilePath, "c");
        umask($saveUMask);

        if($lock === FALSE){
            return FALSE;
        }

        return $locked = flock($lock, LOCK_EX | LOCK_NB);
    }, (Float)$timeout, 0.0);

    if(!$locked){
        throw new Error("Timeout");
    }

    /** @var Resource $lock */

    $actualContents = glob($path->string . DS . "*");
    $isDirectoryNotEmpty = $actualContents !== [$lockFilePath];
    if($isDirectoryNotEmpty){
        fclose($lock);
        throw new Error("Directory is not empty");
    }

    return $lock;
}
