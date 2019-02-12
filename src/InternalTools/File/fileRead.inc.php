<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools\File;

use Closure;
use const LOCK_SH;
use function clearstatcache;
use function filesize;
use function fread;

/**
 * Attempts to read a file within some time limit.
 *
 * @param           String $path
 *
 * @param           Float $secondsLimit
 *
 * @param           Float $secondsDelayBetweenTries
 *
 * @param           Closure|NULL $afterOpenAttempt
 *
 * @param           Closure|NULL $afterLockAttempt
 *
 * @param           Closure|NULL $afterReadAttempt
 *
 * @returns         String|NULL
 */
function fileRead(
    String $path,
    Float $secondsLimit,
    Float $secondsDelayBetweenTries,
    ?Closure $afterOpenAttempt = NULL,
    ?Closure $afterLockAttempt = NULL,
    ?Closure $afterReadAttempt = NULL
): ?String{
    assert(isAbsolutePath($path));

    $contents = NULL;

    retryWithinTimeLimit(function() use(
        &$path, &$contents, &$afterOpenAttempt, &$afterLockAttempt, &$afterReadAttempt
    ){
        try{
            $handle = @fopen($path, "r");
            if($afterOpenAttempt !== NULL) $afterOpenAttempt($handle !== FALSE);
            if($handle === FALSE) return FALSE;

            $lockAcquired = flock($handle, LOCK_SH | LOCK_NB);
            if($afterLockAttempt !== NULL) $afterLockAttempt($lockAcquired);
            if($lockAcquired === FALSE) return FALSE;

            clearstatcache(FALSE, $path);
            $fileSize = filesize($path);
            $contents = $fileSize === 0 ? "" : fread($handle, $fileSize);
            if($afterReadAttempt !== NULL) $afterReadAttempt($contents);

            return TRUE;
        }finally{
            if($handle !== FALSE) fclose($handle);
        }
    }, $secondsLimit, $secondsDelayBetweenTries);

    return $contents;
}
